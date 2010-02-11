<?php

ini_set('date.timezone', 'America/New_York');

define('ACORN_DIR', 'acorn');
define('ROOT_DIR', '.');

require(ACORN_DIR.'/acorn.php');

$time = microtime(true);

Acorn::bootstrap();

Acorn::run($_GET['acorn_url']);

echo microtime(true) - $time;

?>
