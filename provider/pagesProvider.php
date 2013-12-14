<?PHP
namespace pages;
use nrns;



class pagesProvider extends nrns\Provider {
	
	private $templateFilename = 'index.php';
	private $controllerFilename = 'controller.php';
	private $viewGlobals = [];
	
	
	public function __construct($request, $fs, $routeProvider, $rootScope, $injectionProvider, $response, $templateProvider) {
		$this->fs = $fs;
		$this->routeProvider = $routeProvider;
		$this->rootScope = $rootScope;
		$this->injectionProvider = $injectionProvider;
		$this->response = $response;
		$this->templateProvider = $templateProvider;
		$this->request = $request;
	}
	
	public function scan($dir, $baseRoute='/', $baseDir=null) {
		
		
		if(is_string($dir)) {
			$dir = $this->fs->find($dir);
		}
		
		if(!isset($baseDir)) {
			$baseDir = $dir;
			
			$this->routeProvider->when($baseRoute, function($route)use($baseDir){
				$this->renderPage($baseDir, $baseDir);
			});
		}
		
		
		foreach($dir->items() as $item) {
			
			
			if($item->isDir()) {
				
				$filename = $item->getFilename();
				
				if(substr($filename, 0, 1) !== '!') {
					$route = str_replace("__", ":", $baseRoute.'/'.$item->getFilename());
				
					$this->routeProvider->when($route, function($route)use($baseDir, $item){
			
						$this->renderPage($baseDir, $item);
					});
				
					$this->scan($item, $route, $baseDir);
				}
				
			}
			
			
		}
		return $this;
	}
	
	public function notFound($route) {
		$this->routeProvider->otherwise(function($request)use($route){
			$request->redirectRoute($route);
		});
	}
	
	
	public function setTemplateFilename($filename) {
		$this->templateFilename = $filename;
	}
	
	public function setControllerFilename($filename) {
		$this->controllerFilename = $filename;
	}
	
	public function addTemplateGlobal($key, $val) {
		$this->viewGlobals[$key] = $val;
	}
	
	
	
	
	
	private function createView($scope) {
		
		$template = $this->templateProvider->createTemplate($scope);
		
		
		$template->setGlobal('childView', null);
		
		foreach($this->viewGlobals as $key => $val) {
			$template->setGlobal($key, $val);
		}
		
		return $template;
	}
	
	public function renderPage($baseDir, $routeDir) {
		$scope = $this->rootScope;
		$rootView = $view = $this->createView($scope);
		
		$way = $this->findWayFromTo($baseDir, $routeDir);
		$length = count($way);
		
		$doc = \html::doc();
		
		
		foreach($way as $key => $dir) {
			$view->setGlobal('doc', $doc);
			
			if( $dir->exists( $this->controllerFilename ) ) {
				$controller = $dir->find( $this->controllerFilename )->import();
				$this->injectionProvider->invoke($controller, ['scope'=>$scope]);
			}
			
			if( $dir->exists( $this->templateFilename ) ) {
				$view->templatePath( $dir->find( $this->templateFilename ) );
			}
			
			
			
			if($key < ($length-1)) {
				
				$scope = $scope->newChild();
				
				$newView = $this->createView($scope);
				$view->setGlobal('childView', $newView);
				$view = $newView;
			}
			
		}
		
		$doc->head->setBase( $this->request->getBase() );
		$doc->body->setContent($rootView);
		
		$this->response->ContentType('HTML');
		$this->response->setBody( $doc );
		
	}
	
	private function findWayFromTo($from, $to) {
		
		$active = $to;
		$way = [];
		
		while($active !== $from) {
			$way[] = $active;
			$active = $active->parent();
		}
		
		$way[] = $active;
		
		return array_reverse($way);
	}

	
}


?>