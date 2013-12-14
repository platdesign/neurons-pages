<?PHP

	namespace pages;
	use nrns;
	
	
	
	
	
	class template {
		private $templatePath;
		
		public function __construct($scope, $templateProvider, $snippetProvider, $fs) {
			$this->setGlobal('scope', $scope);
			
			$this->templateProvider = $templateProvider;
			$this->snippetProvider = $snippetProvider;
			$this->fs = $fs;
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
		
		public function inc($file, $parseAs='php') {
			if( $file = $this->templatePath->parent()->find($file) ) {
				
				if($parseAs !== 'php') {
					echo $file->parseAs($parseAs);
				} else {
					echo $this->file_get_contents($file, $this->globals);
				}
				
				
			}
		}
		
	}
	


?>