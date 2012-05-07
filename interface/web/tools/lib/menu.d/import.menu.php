<?php


// Menu

if($app->auth->is_admin()) {

$items = array();

$items[] = array( 'title' 	=> 'ISPConfig 3',
				  'target' 	=> 'content',
				  'link'	=> 'tools/import_ispconfig.php');


$module['nav'][] = array(	'title'	=> 'Import',
							'open' 	=> 1,
							'items'	=> $items);

unset($items);
}

?>