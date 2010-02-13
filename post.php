<?php
/**
 *
 * This is a small demonstration of how you can use Acorn without a controller, view, and router.
 * You could even skip the views and models, but then what are you left with?
 * If you're going to leave out all that, stick with regular PHP (i.e. without Acorn).
 *
 * Why would you use Acorn this way? It reduces file clutter and is also faster (performance-wise).
 * There also might be a script where you want to use all the power of Acorn without
 * all the extras (i.e. controllers, router, etc.), this is how you'd do that!
 *
 */
?>
<html>
<head>
	<title>Acorn - Demo</title>
</head>

<body>
	<h1>Welcome to Acorn</h1>
	<p>This is a demonstration of Acorn. You like?</p>
<?php

ini_set('date.timezone', 'America/New_York');

/*
 * Start up Acorn
 *
 */

define('ROOT_DIR', '.');
require('acorn/acorn.php');

$time = microtime(true);

/*
 * Get the post(s)
 *
 */

if (empty($_GET['id']) === false)
{
	$posts = Post::query(Post::$find_by_id." ORDER BY date DESC", $_GET['id']);
}
else
{
	$posts = Post::query("SELECT * FROM #table ORDER BY date DESC");
}

/*
 * Loop through the post(s) and diplay 'em
 *
 */

foreach ($posts as $post)
{
	$link = (isset($_GET['id']) && $_GET['id'] == $post->id) ? $post->title : "<a href='?id={$post->id}'>{$post->title}</a>";
	$date = date('l, \t\h\e jS \of F, Y', strtotime($post->date));
echo <<<EOT
<h2>{$link}</h2>
<p>
<small>Written on {$date}</small>
<br/>
{$post->body}
</p>
<hr/>
EOT;
}

if (isset($_GET['id']))
{
	echo "<a href='post.php'>Go back to full listing</a>";
}

echo '<pre>'.(microtime(true) - $time).'</pre>';

?>
</body>
</html>
