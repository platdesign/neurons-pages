<?PHP
namespace pages;
use nrns;



class pagesProvider extends nrns\Provider {
	
	public function __construct($fs, $routeProvider, $rootScope, $injectionProvider, $response) {
		$this->fs = $fs;
		$this->routeProvider = $routeProvider;
		$this->rootScope = $rootScope;
		$this->injectionProvider = $injectionProvider;
		$this->response = $response;
	}
	
	public function scan($dir, $baseRoute='/', $baseDir=null) {
		
		
		if(is_string($dir)) {
			$dir = $this->fs->find($dir);
		}
		
		if(!$baseDir) {
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
		
	}
	
	private function createView($scope) {
		$view = $this->injectionProvider->invoke('pages\\view', ['scope'=>$scope]);
		$view->setGlobal('childView', null);
		return $view;
	}
	
	public function renderPage($baseDir, $routeDir) {
		$scope = $this->rootScope;
		$rootView = $view = $this->createView($scope);
		
		$way = $this->findWayFromTo($baseDir, $routeDir);
		$length = count($way);
		
		
		foreach($way as $key => $dir) {

			if( $dir->exists('controller.php') ) {
				$controller = $dir->find('controller.php')->import();
				
				$this->injectionProvider->invoke($controller, ['scope'=>$scope]);
			}
			
			if( $dir->exists('template.php') ) {
				$view->templatePath( $dir->find('template.php') );
			}
			
			
			
			if($key < ($length-1)) {
				
				$scope = $scope->newChild();
				
				$newView = $this->createView($scope);
				$view->setGlobal('childView', $newView);
				$view = $newView;
			}
			
		}
		
		$this->response->ContentType('HTML');
		$this->response->setBody( $rootView );
		
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

	public function notFound($route) {
		$this->routeProvider->otherwise(function($request)use($route){
			$request->redirectRoute($route);
		});
	}
}


?>