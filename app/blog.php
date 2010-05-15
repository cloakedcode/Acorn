<?php
$start = microtime();

function post_day($post)
{
	return date('d', strtotime($post->date));
}

function post_month($post)
{
	return date('F', strtotime($post->date));
}

function post_year($post)
{
	return date('Y', strtotime($post->date));
}

function posted_this_year($post)
{
	return ((int)date('Y', strtotime($post->date)) == (int)date('Y'));
}

ini_set('date.timezone', 'America/New_York');

require '../acorn/dev.php';

Acorn::load('model', 'Post');
Acorn::database(array('user' => 'root', 'password' => 'beer', 'host' => 'localhost', 'database' => 'blog', 'adapter' => 'mysql'));
Acorn::defineModel('Tag');

function all()
{
	$posts = Post::query('SELECT * FROM #table ORDER BY date DESC');

	Acorn::$vars['posts'] = $posts;

	Acorn::renderView('./views/blog');
}

function view($params)
{
	$posts = Post::query('SELECT id, title, date, body FROM #table WHERE id = ?', $params['id']);
	$p = $posts[0];

	Acorn::$vars['post'] = $p;

	$next = Post::query('SELECT id, title FROM #table WHERE date > ? ORDER BY date ASC LIMIT 1', $p->date);
	Acorn::$vars['next_post'] = $next[0];

	$prev = Post::query('SELECT id, title FROM #table WHERE date < ? ORDER BY date DESC LIMIT 1', $p->date);
	Acorn::$vars['previous_post'] = $prev[0];

	$tags = $p->tags();
	Acorn::$vars['tags'] = $tags;

	Acorn::renderView('./views/post');
}

function tag($params)
{
	$tags = Tag::query('SELECT id FROM #table WHERE slug = ?', $params['id']);
	$posts = Post::query('SELECT * FROM #table INNER JOIN posts_tags ON #table.id = posts_tags.post_id WHERE posts_tags.tag_id = ? ORDER BY date DESC', $tags[0]->id);

	Acorn::$vars['posts'] = $posts;

	Acorn::renderView('./views/tag');
}

Acorn::route('GET /', 'all');
Acorn::route('GET /:id', 'view');
Acorn::route('GET /:action/:id', 'tag', array('aciton' => 'tag'));

Acorn::run();

echo microtime() - $start;
