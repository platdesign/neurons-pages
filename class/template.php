<?PHP

	namespace pages;
	use nrns;
	
	
	
	
	
	class template {
		private $templatePath;
		
		public function __construct($scope, $templateProvider, $snippetProvider) {
			$this->setGlobal('scope', $scope);
			
			$this->templateProvider = $templateProvider;
			$this->snippetProvider = $snippetProvider;
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
				return $this->file_get_contents($this->templatePath, $this->globals);
			}
		}
		
		public function file_get_contents($file, $globals) {
			if( file_exists($file) ) {
				extract($globals);
				unset($globals);
				ob_start();
					require $file;
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}
		}
		
		public function snippet($ns, $name, $globals=[]) {
			echo $this->snippetProvider->parse($ns, $name, $globals);
		}
		
	}
	


?>