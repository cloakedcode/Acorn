<?php

class PostModel extends AN_Model
{
	function tags()
	{
		return self::query('SELECT name, slug FROM posts INNER JOIN posts_tags ON posts.id = posts_tags.tag_id WHERE posts_tags.post_id = ?', $this->id);
	}
}

?>
