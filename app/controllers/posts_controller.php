<?php

/*
 * This works beautifully, check it out! (http://localhost/acorn/)
 *
 */

class PostsController extends AN_Controller
{
	function index()
	{
		$this->posts = Post::query('SELECT * FROM posts ORDER BY date DESC');
	}

	function view($params)
	{
		if (empty($params['id']))
		{
			$this->post = null;
		}
		else
		{
			$posts = Post::query(Post::$find_by_id, $params['id']);
			$this->post = $posts[0];
		}
	}
}

?>
