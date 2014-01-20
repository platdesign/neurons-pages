<?PHP
namespace pages;
use nrns;



class templateProvider extends nrns\Provider {
	
	private $snippetPaths = [];
	
	public function __construct($injectionProvider, $fs) {
		$this->injectionProvider = $injectionProvider;
		$this->fs = $fs;
	}
	
	public function createTemplate($scope) {
		$temp = $this->injectionProvider->invoke('pages\\template', ['scope'=>$scope]);
		return $temp;
	}

	public function setActive($template) {
		$this->service = $template;
	}

	public function getService() {
		return $this->service;
	}
	
}


?>