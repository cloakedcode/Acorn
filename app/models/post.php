<?php

 class Post extends AN_Model
 {
	 function human_date()
	 {
		 return date('l, \t\h\e jS \of F, Y', strtotime($this->date));
	 }
 }

 ?>
