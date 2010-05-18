<?php

class Post extends AN_Model
{
	function tags()
	{
		return Tag::query('SELECT name, slug FROM #table INNER JOIN posts_tags ON #table.id = posts_tags.tag_id WHERE posts_tags.post_id = ?', $this->id);
	}
}

?>
