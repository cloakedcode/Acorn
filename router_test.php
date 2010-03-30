<?php
/*
 *
 * This is a small demonstration of how you can use Acorn without a controller, view, and router.
 *
 * Why would you use Acorn this way? It reduces file clutter and is also faster (performance-wise).
 *
 */
?>
<html>
<head>
	<title>Acorn - Demo</title>
</head>

<body>
<?php if (empty($_GET['id'])) : ?>
	<h1>Welcome to Acorn</h1>
	<p>This is a demonstration of Acorn. You like?</p>
<?php endif;

ini_set('date.timezone', 'America/New_York');

/*
 * Start up Acorn
 *
 */

define('ROOT_DIR', '.');
require('acorn/acorn.php');

$time = microtime(true);

Acorn::route('GET /skipper', 'sea');
Acorn::route('GET /p/:num', 'digits', array('num' => 46), array('num' => '\d+'));
Acorn::run();

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
