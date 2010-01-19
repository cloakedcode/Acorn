<?php

class AN_Inflector
{
	static function underscore($str)
	{
		static $scored = array();

		if (empty($scored[$str]))
		{
			$scored[$str] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
		}

		return $scored[$str];
	}

	static function camelize($str)
	{
		static $camels = array();

		if (empty($camels[$str]))
		{
			$camels[$str] = str_ireplace(' ', '', ucwords(str_ireplace('_', ' ', $str)));
		}

		return $camels[$str];
	}

	static function tableize($str)
	{
		return self::underscore($str).'s';
	}
}

?>
