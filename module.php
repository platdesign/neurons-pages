<?PHP

	namespace nrns;
	use nrns;
	
	

	$module = nrns::module("pages", ['router', 'fs', 'snippets']);

	$module->config(function(){
	
		
		require 'provider/pagesProvider.php';
		require 'provider/templateProvider.php';
		
		require 'class/template.php';
		
		require 'lib/html.php';
		
	});

	
	$module->provider("templateProvider", "pages\\templateProvider");
	$module->provider("pagesProvider", "pages\\pagesProvider");
	
?>