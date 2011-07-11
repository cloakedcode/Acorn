<?php

ini_set('date.timezone', 'America/New_York');
require('acorn/acorn.php');

$time = microtime(true);
Acorn::run();
echo microtime(true) - $time;

?>
