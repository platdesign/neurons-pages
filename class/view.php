<?PHP

	namespace pages;
	use nrns;
	
	
	
	
	
	class view {
		private $templatePath;
		
		public function __construct($scope) {
			$this->setGlobal('scope', $scope);
		}
		
		public function setGlobal($key, $val) {
			$this->globals[$key] = $val;
		}
		
		public function templatePath($path) {
			$this->templatePath = $path;
		}
		
		public function __tostring() {
			return (string) $this->render();
		}
		
		public function render() {
			if($this->templatePath) {
				return nrns::template($this->templatePath, $this->globals);
			}
		}
	}
	


?>