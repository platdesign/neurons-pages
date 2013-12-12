<?PHP

	namespace nrns;
	use nrns;
	
	

	$module = nrns::module("pages", ['router', 'fs']);

	$module->config(function(){
	
		
		require 'provider/pagesProvider.php';
		require 'class/view.php';
		
	});

	$module->provider("pagesProvider", "pages\\pagesProvider");
	
?>