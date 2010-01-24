<?php

/*
 * This doesn't work, yet, it's simply a demo of how Acorn would be used.
 *
 */

 class SampleController extends AN_Controller
 {
	 function index()
	 {
		 $this->posts = Post::query(Post::$find_by_id, 3079);

		 // This line is still needed, renderView will (eventually) automatically be called if $this->should_render is true (it is be default).
		 $this->renderView('index');
	 }
 }

 ?>
