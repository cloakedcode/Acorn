<?php

function __autoload($class)
{
	if (substr($class, 0, 2) === 'AN')
	{
		require(ACORN_DIR.'/'.strtolower(substr($class, 3)).'.php');
	}
	else if (substr($class, -10) === 'Controller')
	{
		Acorn::load('controller', substr($class, 0, -10));
	}
}

class Acorn
{
	static public $include_paths = array('app');

	/**
	 * Loads file named $name.
	 *
	 * <code>
	 * <?php Acorn::load('controller', 'Users'); ?>
	 * </code>
	 * 
	 * @param string $type Type of file.
	 * @param string $name Name of file.
	 * @static
	 * @see filePath
	 * @access public
	 * @return bool True if successfully loaded file.
	 */
	static function load($type, $name)
	{
		$path = self::filePath($type, $name);

		if ($path !== false)
		{
			include($path);
		}
	}

	/**
	 * Searches Acorn::config('include_paths') for the specified file. 
	 * 
	 * <code>
	 * <?php $path = Acorn::filePath('controller', 'Users'); ?>
	 * </code>
	 *
	 * @param string $type Type of file (e.g. 'controller' or 'model').
	 * @param string $name Name of file (e.g. 'Application' or 'User').
	 * @static
	 * @access public
	 * @return string|false False if no file exists, otherwise path of file.
	 */
	static function filePath($type, $name)
	{
		$type = strtolower($type);
		$name = strtolower($name);

		if ($type === 'layout')
		{
			$type = 'view';
			$name = 'layouts/'.$name;
		}

		if ($type !== 'layout' && $type !== 'view')
		{
			$filename = "{$name}_{$type}.php";
		}
		else
		{
			$filename = $name.'.phtml';
		}

		$path = "/{$type}s/{$filename}";
		foreach (self::$include_paths as $inc)
		{
			$file = $inc.$path;

			if (file_exists($file))
			{
				return $file;
			}
		}

		return false;
	}
}

?>
