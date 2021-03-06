<?php

/*!
Copyright (c) 2010 Alan Smith

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

if (defined('ACORN_DIR') === false)
{
	define('ACORN_DIR', dirname(__FILE__));
}

define('ACORN_CLEAN_URLS', true);
$url = ACORN_CLEAN_URLS ? dirname($_SERVER['SCRIPT_NAME']) : $_SERVER['SCRIPT_NAME'];
define('ACORN_URL', ($url === '/') ? $url : $url.'/');
define('ACORN_VERSION', 0.9);

spl_autoload_register('acorn_autoload');

function acorn_autoload($class)
{
	return Acorn::load('model', $class);
}

/**
 * This is the class you'll be using the most. You don't need to know much about the other classes except <AN_Model>.
 *
 * @see AN_Model model
 */
class Acorn
{
	static public $params = array();
	static public $vars = array();
	static public $view_contents = '';
	static public $cache_path = './cache';
	static public $include_paths = array('.');

	static private $routes = array();

	/**
	 * Bootstraps Acorn. Is called automatically when Acorn loaded. You never need to call this function directly.
         *
         * @api private
	 */
	static function _bootstrap()
	{
		static $strapped = false;

		if ($strapped === false)
		{
			$strapped = true;

			AN_Event::run('acorn.bootstrap');
		}
	}

	/**
	 * Alias for AN_Model::defineModel
	 *
         * @see AN_Model::defineModel model.defineModel
	 */
	static function defineModel($name, $parent = 'AN_Model')
	{
		AN_Model::defineModel($name, $parent);
	}

	/**
	 * Returns database connection using the specified settings. Required keys are: user, password, database, host, adapter.
	 *
	 *     <?php Acorn::database(array('user' => 'skrat', 'password' => 'nut', 'database' => 'acorn_tree', 'host' => 'localhost', 'adapter' => 'mysql')) ?>
	 *
	 * @return {AN_Database} Database connection.
	 * @see AN_Database database
	 */
	static function database($config = array())
	{
		static $db = null;

		$base = null;
		AN_Event::runFilterOnData('acorn.database', $base);
		
		if (empty($base) === false)
		{
			return $base;
		}

		if ($db === null)
		{
			$db = new AN_Database($config);
		}

		return $db;
	}

	/**
	 * Sets status and displays error.
	 * 
	 * @param {int} code Error code
	 */
	static function error($code)
	{
		header("HTTP/1.0 {$code}");
		header("Status: {$code}");

		AN_Event::run('acorn.error', $code);
	}

	/**
	 * Loads named file with the given type.
	 *
	 *     <?php Acorn::load('model', 'User'); ?>
	 * 
	 * @param {String} type Type of file.
	 * @param {String} name - Name of file.
	 * @return {bool} True if successfully loaded file.
	 * @see filePath
	 */
	static function load($type, $name)
	{
		$event_params = array('type' => &$type, 'name' => &$name);
		AN_Event::runFilterOnData('acorn.will_load_file', $event_params);

		$path = self::filePath($type, $name);

		if ($path !== false)
		{
			AN_Event::runFilterOnData('acorn.will_load_file_at_path', $path);

			$type = strtolower($type);
			if ($type === 'model')
			{
				$path = "anmodel://{$path}";
			}

			include($path);

			AN_Event::run('acorn.did_load_file', $path);

			return true;
		}

		return false;
	}

	/**
	 * Searches Acorn::$include_paths for the specified file. 
	 * 
	 *     <?php $path = Acorn::filePath('model', 'User'); ?>
	 *
	 * @param {String} type Type of file - it gets converted to lowercase (e.g. 'model').
	 * @param {String} name Name of file - it gets converted to lowercase (e.g. 'User').
	 *
	 * @return {bool|String} False if no file exists, otherwise path of file.
	 */
	static function filePath($type, $name)
	{
		$path = '';

		$event_params = array('type' => &$type, 'name' => &$name, 'path' => &$path);

		if (AN_Event::runFilterOnData('acorn.file_path', $event_params) === false)
		{
			$type = strtolower($type);
			$name = strtolower($name);
		}

		if (empty($path) === false)
		{
			return $path;
		}
		else
		{
			$filename = (AN_Event::runFilterOnData('acorn.file_name', $name)) ? $name : $name.'.php';

			return self::pathForFile($filename);
		}

		return false;
	}


	static function pathForFile($filename)
	{
		$paths = self::$include_paths;

		AN_Event::runFilterOnData('acorn.include_paths', $paths);
		
		foreach ($paths as $inc)
		{
			$file = $inc.'/'.$filename;

			if (file_exists($file))
			{
				return $file;
			}
		}
		
		return false;
	}

	/**
	 * Renders the view at the specified path.
	 * 
	 *     <?php Acorn::renderView('./users/info'); ?>
	 * 
	 * @param {String} file Path to the view file.
	 * @param {String} layout Name of the layout to render the view in. If null no layout will be used. (Default: layout)
	 *
	 * @see filePath
	 */
	static function renderView($file, $layout = 'layout')
	{
		$__path = self::filePath('view', $file);
		unset($file);

		if ($__path !== false)
		{
			ob_start();

			$__layout = $layout;
			unset($layout);

			AN_Event::runFilterOnData('acorn.will_render_view', $__path);

			extract((array)self::$vars, EXTR_OVERWRITE);

			include('anview://'.$__path);
			self::$view_contents = ob_get_clean();

			$__layout_path = ($__layout !== null) ? self::filePath('layout', $__layout) : false;

			if ($__layout_path !== false)
			{
				AN_Event::runFilterOnData('acorn.will_render_layout', $__layout_path);
				include('anview://'.$__layout_path);
			}
			else
			{
				AN_Event::run('acorn.will_render_without_layout');

				echo self::$view_contents;
			}
		}
	}

	/**
	 * Renders a partial with the specified name and returns it as a string. 
	 * 
	 * @param {String} name Name of the partial to render (e.g. 'posts/detail' would be 'posts/_detail.php' on disk).
	 * @param {Mixed} var Variable to be given the name of the partial (e.g. name = 'posts/detail', $detail = $var).
	 * @param {Array} extra_vars Variables for the partial to use. (e.g. array('user' => $user, 'time' => time()))
	 *
	 * @return {String} Contents of the rendered partial.
	 */
	static function renderPartial($name, $var, $extra_vars = array())
	{
		$file = $name;

		if (AN_Event::runFilterOnData('acorn.partial_base_file_name', $name) === false)
		{
			$dir = dirname($name);
			$name = basename($name);
			$file = (empty($dir)) ? '_'.$name : $dir.'/_'.$name;
		}

		$__path = self::filePath('view', $file);

		if ($__path !== false)
		{
			$extra_vars[$name] = $var;
			extract($extra_vars, EXTR_OVERWRITE);

			ob_start();

			AN_Event::runFilterOnData('acorn.will_render_partial', $name);
			include('anview://'.$__path);

			return ob_get_clean();
		}

		return null;
	}	

	/**
	 * Connects a URL to a callback with default parameters and regular expression requirements. 
	 * 
	 *     <?php
	 *     Acorn::route('GET /posts/:id', 'viewPost', null, array('id' => '\d+'));
	 *     Acorn::route('GET /pages/:name', 'viewPage', array('name' => 'home'));
	 *     ?>
	 *
	 * @param {String} url The URL to match.
	 * @param {Mixed} callback The callback to be used when this route is matched.
	 * @param {Array} defaults The default paramenters to be passed to the callback.
	 * @param {Array} regex Regular expressions to define the segments in the URL.
	 */
	static function route($url, $callback, $defaults = array(), $regex = array())
	{
		if (is_string($callback) === false && (is_array($callback) && count($callback) < 2))
		{
			throw new Exception('Invalid callback.');
		}
		
		$keys = array();
		$orig_url = substr($url, strpos($url, ' ') + 1);
		$url = str_ireplace('/', '\/', $url);

		if (preg_match_all('/:([a-zA-Z0-9]+)/', $url, $matches))
		{
			array_shift($matches);

			foreach ($matches[0] as $key)
			{
				$reg = (empty($regex[$key])) ? '[^\/;,\.\?]*' : $regex[$key];
				$reg = "(?P<{$key}>{$reg})";

				if (isset($defaults[$key]))
				{
					$reg = "?{$reg}?";
				}

				$url = str_replace(':'.$key, $reg, $url);
				$keys[] = $key;
			}
		}

		$bits = explode(' ', $url, 2);
		$bits[0] = '('.$bits[0].')';

		self::$routes[$bits[0].' '.$bits[1].'$'] = array($callback, (array)$defaults, $keys, $orig_url);
	}

	/**
	 * Converts the given parameters into a URL appropriate for a link.
	 * 	
	 *     <?php echo Acorn::url(array('action' => 'view', 'id' => 1)); ?>
	 * 
	 * @param {Array} params Array of key/value pairs to match to a route.
	 * @param {String} method Request method that matches the method at the beginning of the route.
	 *
	 * @return {String} The URL as a string if a route is found. Null if no match is made.
	 */
	static function url($params, $method = 'get')
	{
		$method = strtoupper($method);
		$event_params = array('params' => &$params, 'method' => &$method);

		if (AN_Event::runFilterOnData('acorn.will_route_params', $event_params) && isset($event_params['url']))
		{
			return $event_params['url'];
		}

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
				if (preg_match('/'.$url_pattern.'/', $method.' '.$url))
				{
					return ACORN_URL.substr($url, 1);
				}
			}
		}

		return null;
	}

	/**
	 * Calls the first matched route for $url. If no parameters are passed the current request method and request uri are used.
	 * 
	 * @param {String} url The URL to route. Must have a request method at the beginning (e.g. "GET /").
	 */
	static function run($url = null)
	{
		if ($url === null)
		{
			if (isset($_SERVER['PATH_INFO']))
			{
				$request = $_SERVER['PATH_INFO'];
			}
			else if (preg_match('/^(\/.*?)(\?|&).*$/', $_SERVER['QUERY_STRING'], $matches))
			{
				$request = $matches[1];
			}
			else if (empty($_SERVER['QUERY_STRING']) === false && $_SERVER['QUERY_STRING']{0} === '/')
			{
				$request = $_SERVER['QUERY_STRING'];
			}
			else
			{
				$request = '/';
			}

			$url = $_SERVER['REQUEST_METHOD'].' '.$request;
		}

		$url = ($url{strlen($url)-1} == ' ') ? 'GET /' : $url;

		AN_Event::runFilterOnData('acorn.will_route_url', $url);

		foreach (self::$routes as $url_pattern => $data)
		{
			if (preg_match('/'.$url_pattern.'/', $url, $matches) > 0)
			{
				array_shift($matches);

				foreach ($matches as $key => $value)
				{
					if (is_int($key))
					{
						unset($matches[$key]);
					}
				}

				$params = array_merge($data[1], array_filter($matches));

				$event_params = array('route' => &$data[0], 'params' => &$params);
				AN_Event::runFilterOnData('acorn.will_execute_route', $event_params);
				call_user_func($data[0], $params);

				return true;
			}
		}

		self::error(404);

		return false;
	}

	/**
	 * Converts string to lowercase and replaces lowercase letter followed by a uppercase letter with the lowercase
	 * version of both separated by an underscore.
	 * 
	 * @param {String} str String to underscore.
	 *
	 * @return {String} Underscored version of string.
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
