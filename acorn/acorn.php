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
	static public $include_paths = array();

	/**
	 * Bootstraps Acorn. Should be called right after Acorn is included. 
	 * 
	 * @static
	 * @access public
	 */
	static function bootstrap()
	{
		$inc = self::config('include_paths');

		if ($inc !== false && is_array($inc))
		{
			self::$include_paths = $inc;
		}
	}

	/**
	 * Returns config for $key. 
	 * 
	 * @param string $key Name of value to get from config file.
	 * @param string $file Name of file in config dir.
	 * @static
	 * @access public
	 * @return mixed False if $key is not found, otherwise value at $key.
	 */
	static function config($key, $file = 'config')
	{
		static $configs = array();

		if (isset($configs[$file]) === false)
		{
			$path = ROOT_DIR.'/config/'.$file.'.php';

			if (file_exists($path))
			{
				include($path);

				$configs[$file] = $config;
			}
			else
			{
				$configs[$file] = false;
			}
		}

		return (empty($configs[$file][$key])) ? false : $configs[$file][$key];
	}

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
