<?php

class PostModel extends AN_Model
{
	static $find_by_id = 'SELECT * FROM #table WHERE id = ?';

	function human_date()
	{
		return date('l, \t\h\e jS \of F, Y', strtotime($this->date));
	}
}

?>
