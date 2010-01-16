<?php

/*
 * This doesn't work, yet, it's simply a demo of how Acorn would be used.
 *
 */

 class SampleController extends Controller
 {
	 function index()
	 {
		 $this->msg = 'Hello World!';

		 // This line isn't needed, renderView is called automatically if $this->should_render is true.
		 $this->renderView('index');
	 }
 }

 ?>
