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
		$und = Acorn::underscore(substr($class, 3));
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

/*
 * Class: Acorn 
 * This is the class you'll be using the most. You don't need to know much about the other classes except <AN_Model>.
 *
 * See Also:
 * 	<AN_Model>
 */
class Acorn
{
	static public $params = array();
	static public $vars = array();

	static private $routes = array();

	/*
	 * Method: _bootstrap
	 * Bootstraps Acorn. Is called automatically when Acorn loaded. You never need to call this function directly.
	 */
	static function _bootstrap()
	{
		static $strapped = false;

		if ($strapped === false)
		{
			$strapped = true;
		}
	}

	/*
	 * Method: defineModel
	 * Alias for <AN_Model::defineModel>.
	 *
	 * See Also:
	 * 	<AN_Model::defineModel>
	 */
	static function defineModel($name, $parent = 'AN_Model')
	{
		$code = <<<EOD
class {$name} extends {$parent}
{
	static function query()
	{
		\$args = func_get_args();
		array_unshift(\$args, '{$name}');
		return call_user_func_array(array('{$parent}', 'query'), \$args);
	}
}
EOD;
		eval($code);
		
	}

	/*
	 * Method: database
	 * Returns database connection using the specified settings. Required keys are: user, password, database, host, adapter.
	 *
	 * : <?php Acorn::database(array('user' => 'skrat', 'password' => 'nut', 'database' => 'acorn_tree', 'host' => 'localhost', 'adapter' => 'mysql')) ?>
	 *
	 * Returns:
	 * 	AN_Database - Database connection.
	 * 
	 * See Also:
	 * 	<AN_Database>
	 */
	static function database($config = array())
	{
		static $db = null;

		if ($db === null)
		{
			$db = new AN_Database($config);
		}

		return $db;
	}

	/*
	 * Method: error
	 * Sets status and displays error.
	 * 
	 * Parameters:
	 	int $code - Error code
	 */
	static function error($code)
	{
		header("Status: {$code}");

		echo $code;
	}

	/*
	 * Method: load
	 * Loads named file with the given type.
	 *
	 * : <?php Acorn::load('controller', 'Users'); ?>
	 * 
	 * Parameters:
	 * 	string $type - Type of file.
	 * 	string $name - Name of file.
	 *
	 * Returns:
	 * 	bool - True if successfully loaded file.
	 *
	 * See Also:
	 *	<filePath>
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

	/*
	 * Method: filePath
	 * Searches Acorn::config('include_paths') for the specified file. 
	 * 
	 * : <?php $path = Acorn::filePath('controller', 'Users'); ?>
	 *
	 * Parameters:
	 * 	string $type - Type of file (e.g. 'controller' or 'model').
	 * 	string $name - Name of file (e.g. 'Application' or 'User').
	 *
	 * Returns:
	 * 	mixed False if no file exists, otherwise path of file.
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

		if ($type === 'controller' || $type === 'helpers')
		{
			$filename .= "_{$type}.php";
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

	/*
	 * Method: renderView
	 * Renders a view with the specified name.
	 * 
	 * : <?php Acorn::renderView('users/info'); ?>
	 * 
	 * Parameters:
	 * 	string $name - Name of the view file.
	 * 	string $layout - Name of the layout to render the view in. If null no layout will be used. (Default: layout)
	 */
	static function renderView($name, $layout = 'layout')
	{
		$__path = self::filePath('view', $name);

		if ($__path !== false)
		{
			$__layout = $layout;
			extract((array)self::$vars, EXTR_OVERWRITE);

			ob_start();
			include($__path);
			self::$view_contents = ob_get_clean();

			$__layout_path = ($layout !== null) ? self::filePath('layout', $__layout) : false;

			if ($__layout_path !== false)
			{
				include($__layout_path);
			}
			else
			{
				echo self::$view_contents;
			}
		}
	}

	/*
	 * Method: renderPartial
	 * Renders a partial with the specified name and returns it as a string. 
	 * 
	 * Parameters:
	 * 	string $name - Name of the partial to render (e.g. 'posts/detail' would be 'posts/_detail.php' on disk).
	 * 	mixed $var 
	 * 	array $extra_vars - Variables for the partial to use. (e.g. array('user' => $user, 'time' => time()))
	 *
	 * Returns:
	 * 	string Contents of the rendered partial.
	 */
	static function renderPartial($name, $var, $extra_vars = array())
	{
		$dir = dirname($name);
		$name = basename($name);
		$file = (empty($dir)) ? '_'.$name : $dir.'/_'.$name;

		$__path = self::filePath('view', $file);

		if ($__path !== false)
		{
			$extra_vars[$name] = $var;
			extract($extra_vars, EXTR_OVERWRITE);

			ob_start();
			include($__path);
			return ob_get_clean();
		}

		return null;
	}	

	/*
	 * Method: route
	 * Connects a URL to a callback with default parameters and regular expression requirements. 
	 * 
	 * : <?php
	 * : Acorn::route('GET /posts/:id', 'viewPost', null, array('id' => '\d+'));
	 * : Acorn::route('GET /pages/:name', 'viewPage', array('name' => 'home'));
	 * : ?>
	 *
	 * Parameters:
	 * 	string $url - The URL to match.
	 * 	mixed $callback - The callback to be used when this route is matched.
	 * 	array $defaults - The default paramenters to be passed to the callback.
	 * 	array $regex - Regular expressions to define the segments in the URL.
	 */
	static function route($url, $callback, $defaults = array(), $regex = array())
	{
		if (is_string($callback) === false && (is_array($callback) && count($callback) < 2) === false)
		{
			throw new Exception('Invalid callback.');
		}
		
		$keys = array();
		$orig_url = substr($url, strpos($url, ' ') + 1);

		if (preg_match_all('/:([a-zA-Z0-9]+)/', $url, $matches))
		{
			array_shift($matches);

			foreach ($matches[0] as $key)
			{
				$reg = (empty($regex[$key])) ? '[^/;,\.\?]*' : $regex[$key];
				$reg = "(?P<{$key}>{$reg})";

				if (isset($defaults[$key]))
				{
					$reg .= '?';
				}

				$url = str_replace(':'.$key, $reg, $url);
				$keys[] = $key;
			}
		}

		self::$routes[$url] = array($callback, (array)$defaults, $keys, $orig_url);
	}

	/*
	 * Method: url
	 * Converts the given parameters into a URL appropriate for a link.
	 * 	
	 * : <?php echo Acorn::url(array('action' => 'view', 'id' => 1)); ?>
	 * 
	 * Parameters:
	 * 	array $params - Array of key/value pairs to match to a route.
	 * 	string $method - Request method that matches the method at the beginning of the route.
	 *
	 * Returns:
	 * 	The URL as a string if a route is found. Null if no match is made.
	 */
	static function url($params, $method = 'get')
	{
		$method = strtoupper($method);

		foreach (self::$routes as $url_pattern => $data)
		{
			$halt_defaults = false;
			$url = $data[3];
			$filled_keys = 0;

			foreach ($data[2] as $key)
			{
				if ($halt_defaults === false && empty($params[$key]))
				{
					$val = $data[1][$key];
				}
				else if (isset($params[$key]))
				{
					$halt_defaults = true;
					$val = $params[$key];
				}
				else
				{
					break;
				}

				$url = preg_replace("|:{$key}|", $val, $url);
				$filled_keys += 1;
			}

			if ($filled_keys === count($data[2]) && $filled_keys >= count($params))
			{
				if (preg_match('|'.$url_pattern.'|', $method.' '.$url))
				{
					return $url;
				}
			}
		}

		return null;
	}

	/*
	 * Method: run 
	 * Calls the first matched route for $url. If no parameters are passed the current request method and request uri are used.
	 * 
	 * Parameters:
	 * 	string $url - The URL to route. Must have a request method at the beginning (e.g. "GET /").
	 */
	static function run($url = null)
	{
		if ($url === null)
		{
			$url = $_SERVER['REQUEST_METHOD'].' '.substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
		}

		foreach (self::$routes as $url_pattern => $data)
		{
			if (preg_match('|'.$url_pattern.'|', $url, $matches) > 0)
			{
				array_shift($matches);
				$params = array_merge($data[1], $matches);

				call_user_func($data[0], $params);

				break;
			}
		}
	}

	/*
	 * Method: underscore 
	 * Converts string to lowercase and replaces lowercase letter followed by a uppercase letter with the lowercase
	 * version of both separated by an underscore.
	 * 
	 * Parameters:
	 * 	string $str - String to underscore.
	 *
	 * Returns:
	 * 	Underscored version of string.
	 */
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

Acorn::_bootstrap();

?>
