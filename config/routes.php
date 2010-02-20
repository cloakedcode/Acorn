<?php

/**
 * Bah! This is too clunky and not easy to make a two-way router out of.
 * This has been ditched for a two-way router that is far more useful and powerful.
 */
/*
$routes[] = "|(?<controller>.*?)/(?<action>.*?)/(?<id>\d+)|";

$routes[] = "|(?<controller>.*?)/(?<action>[^/]*)/?|";
*/

$router->root('sample#index');

$router->connect(':controller/:action/:id', array(), array('id' => '\d+'));

// The next two are equivalent but the first is much easier to read and type. Double win!
$router->connect(':controller/:action', '#index');
// $router->connect(':controller/:action', array('action' => 'index'));

?>
