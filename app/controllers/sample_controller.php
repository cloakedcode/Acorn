<?php

/*
 * This works beautifully, check it out! (http://localhost/acorn/)
 *
 */

 class SampleController extends AN_Controller
 {
	 function index()
	 {
		 $this->posts = Post::query(Post::$find_by_id, 3079);

		 $this->renderView('index');
	 }
 }

 ?>
