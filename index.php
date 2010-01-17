<?php

define('ACORN_DIR', 'acorn');

require(ACORN_DIR.'/acorn.php');

$con = new SampleController;

$con->index();

?>
