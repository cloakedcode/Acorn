<html>
<head>
	<title>Acorn - Demo</title>
</head>

<body>
<?php

ini_set('date.timezone', 'America/New_York');

/*
 * Start up Acorn
 *
 */

require('acorn/acorn.php');

$time = microtime(true);

Acorn::route('GET /', 'index');
// /router_test.php/skipper
Acorn::route('GET /skipper/*', 'sea');
// /router_test.php/p/20
Acorn::route('GET /p/:num', 'digits', array('num' => 46), array('num' => '\d+'));

Acorn::run();

function index()
{
?>
	<h1>Welcome to Acorn</h1>
	<p>This is a demonstration of Acorn. You like?</p>
<?php
}
function sea($params)
{
	var_dump($params);
}

function digits($params)
{
	var_dump($params);
	echo Acorn::url(array('num' => 10));
}

echo '<pre>'.(microtime(true) - $time).'</pre>';

?>
</body>
</html>
