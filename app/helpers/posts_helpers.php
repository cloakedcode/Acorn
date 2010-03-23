<?php

function human_date($post)
{
	 return date('l, \t\h\e jS \of F, Y', strtotime($post->date));
}

 ?>
