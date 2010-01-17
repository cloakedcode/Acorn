<?php

define('ACORN_DIR', 'acorn');
define('ROOT_DIR', '.');

require(ACORN_DIR.'/acorn.php');

$time = microtime(true);

Acorn::bootstrap();

$con = new SampleController;

$con->index();

echo microtime(true) - $time;

?>
