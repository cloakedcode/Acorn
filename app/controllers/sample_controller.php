<?php

/*
 * This doesn't work, yet, it's simply a demo of how Acorn would be used.
 *
 */

 class SampleController extends AN_Controller
 {
	 function index()
	 {
		 $this->posts = AN_Model::query('Post', 'SELECT * FROM #table ORDER BY date DESC');

		 // This line is still needed, renderView will (eventually) automatically be called if $this->should_render is true (it is be default).
		 $this->renderView('index');
	 }
 }

 ?>
