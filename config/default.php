<?php


$config['enviroment'] = 'development';
$config['log_path'] = phial_path.'_logs/'.date("Y-m-d").'.phial.log';


/*
 ~~~ uncomment this section to enable mysql integration ~~~~~

$config['database'] = array(
	'connection'	=> "mysql://root:root@localhost/phial?charset=utf8",
	'models'		=> phial_path.'models'
);
*/



return $config;