<?php

ini_set('date.timezone', 'America/New_York');
define('ROOT_DIR', '.');

require('acorn/acorn.php');

$url = (empty($_GET['acorn_url'])) ? '' : $_GET['acorn_url'];
$time = microtime(true);

Acorn::run($url);

echo microtime(true) - $time;

?>
