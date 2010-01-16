<?php

/*
 * This doesn't work, yet, it's simply a demo of how Acorn would be used.
 *
 */

 class SampleController extends AN_Controller
 {
	 function index()
	 {
		 $this->msg = 'Hello World!';

		 // This line isn't needed, renderView is called automatically if $this->should_render is true (it is be default).
		 $this->renderView('index');
	 }
 }

 ?>
