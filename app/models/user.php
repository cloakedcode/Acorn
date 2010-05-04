<?php

class UserModel extends AN_Model
{
	static function hashPassword($str)
	{
		return sha1($str.'a very long and complicated hash salt');
	}
}

?>
