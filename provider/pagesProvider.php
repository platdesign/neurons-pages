<?PHP
namespace pages;
use nrns;



class pagesProvider extends nrns\Provider {
	
	private $templateFilename = 'index.php';
	private $controllerFilename = 'controller.php';
	private $viewGlobals = [];
	
	
	public function __construct($nrns, $request, $fs, $routeProvider, $rootScope, $injectionProvider, $response, $templateProvider) {
		$this->fs = $fs;
		$this->routeProvider = $routeProvider;
		$this->rootScope = $rootScope;
		$this->injectionProvider = $injectionProvider;
		$this->response = $response;
		$this->templateProvider = $templateProvider;
		$this->request = $request;
		$this->nrns = $nrns;
	}
	
	public function scan($dirPath, $baseRoute='/', $baseDir=null) {
		$registry = $this->injectionProvider->service('registry');
		$reg = $registry('neurons.pages');

		$routes = [];
		$dir = $this->fs->find($dirPath);


		if( $this->nrns->devMode ) {

			if( $dir ) {

				$tmp = $dir->dirs(true);



				foreach($tmp as $item) {
					if($item->isDir()) {
						$path = str_replace($dir, '', $item->getPathname());
						$route = str_replace('__', ':', $path);

						$routes[] = (object) [
							'route'	=>	$route,
							'path'	=>	$path
						];
					}
				}

				$reg->set('routes', $routes);

			} else {
				throw nrns::Exception('PagesProvider: Dir not found ('.$dirPath.')');
			}
		} else {
			$routes = $reg->get('routes');
		}
		
		

		foreach($routes as $route) {
			$this->routeProvider->when($route->route, function()use($route, $dir){
				$this->renderPage($dir, $route->path);
			});
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

		

		$routeDir = $baseDir->find($routeDir);


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
		
		$doc->head->setBase( $this->request->getBase()."/" );
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