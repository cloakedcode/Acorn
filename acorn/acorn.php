<?php

if (defined('ACORN_DIR') === false)
{
	define('ACORN_DIR', dirname(__FILE__));
}

define('ACORN_URL', dirname($_SERVER['SCRIPT_NAME']).'/');

function __autoload($class)
{
	if (substr($class, 0, 2) === 'AN')
	{
		$und = AN_Inflector::underscore(substr($class, 3));
		$pos = strrpos($und, '_');
		$name = ($pos !== false) ? substr($und, 0, $pos) : $und;

		require(ACORN_DIR.'/'.$name.'.php');
	}
	else if (substr($class, -10) === 'Controller')
	{
		Acorn::load('controller', substr($class, 0, -10));
	}
	else
	{
		Acorn::load('model', $class);
	}
}

class Acorn
{
	static public $include_paths = array();
	static public $params = array();

	/**
	 * Bootstraps Acorn. Is called automatically when Acorn loaded. You never need to call this function directly.
	 * 
	 * @static
	 * @access public
	 */
	static function _bootstrap()
	{
		static $strapped = false;

		if ($strapped === false)
		{
			require(ACORN_DIR.'/inflector.php');

			$inc = self::config('include_paths');

			if ($inc !== false && is_array($inc))
			{
				self::$include_paths = $inc;
			}
			
			$strapped = true;
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
	 * Returns database using info from config (i.e. Acorn::config('database'))
	 * 
	 * @static
	 * @access public
	 * @see AN_Database
	 * @return AN_Database
	 */
	static function database()
	{
		static $db = null;

		if ($db === null)
		{
			$db = new AN_Database(self::config('database'));
		}

		return $db;
	}

	/**
	 * Sets status and displays error.
	 * 
	 * @param int $code Error code
	 * @static
	 * @access public
	 */
	static function error($code)
	{
		header("Status: {$code}");

		echo $code;
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

			$type = strtolower($type);
			if ($type === 'controller')
			{
				AN_Controller::_loadedController($name);
			}
			else if ($type === 'model')
			{
				AN_Model::_loadedModel($name);
			}

			return true;
		}

		return false;
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

		$filename = $name;

		if ($type === 'controller')
		{
			$filename .= "_{$type}.php";
		}
		elseif ($type === 'helpers')
		{
			$filename .= "_{$type}.php";
		}
		elseif ($type === 'view' || $type === 'layout')
		{
			$filename .= '.phtml';
		}
		else
		{
			$filename .= '.php';
		}

		$path = '/'.rtrim($type, 's')."s/{$filename}";
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

	/**
	 * Returns the Acorn router. 
	 * 
	 * @static
	 * @access public
	 * @return object
	 */
	static function router()
	{
		static $router = null;

		if ($router === null)
		{
			$router = new AN_Router(ROOT_DIR.'/config/routes.php');
		}

		return $router;
	}

	/**
	 * Parses a URL (uses the router) and loads the resulting controller and calls the action.
	 * 
	 * @param string $url URL to parse
	 * @static
	 * @access public
	 */
	static function run($url)
	{
		$params = self::router()->urlToParams($url);
		self::$params = $params;

		if (empty($params['controller']) === false)
		{
			$class = AN_Inflector::camelize($params['controller']);

			if (self::load('controller', $class))
			{
				$class .= 'Controller';

				$controller = new $class;

				$controller->callAction($params['action']);
			}
			else
			{
				self::error(404);
			}
		}
		else
		{
			self::error(404);
		}
	}

	static function toURL($params)
	{
		return ACORN_URL.self::router()->paramsToURL($params);
	}
}

Acorn::_bootstrap();

?>
