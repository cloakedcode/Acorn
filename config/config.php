<?php

// Set the environment.
$config['env'] = 'debug';

// Places to look for controllers, models, and views.
$config['include_paths'] = array('app');

// Set-up a whole bunch of database configurations.
$databases = array(
	'debug' => array(
		'adapter' =>'mysql',
		'host' =>'localhost',
		'database' => 'blog',
		'user' => 'root',
		'password' => 'beer'
		)
);

// Set database config based on current environment. Allows for multiple database configurations like RoR.
$config['database'] = $databases[$config['env']];

?>
