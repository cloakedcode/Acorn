<?php

/*
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

define('ACORN_VERSION', 0.9);
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
		AN_Model::defineModel($name, $parent);
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
<?php

/*
 * Class: AN_Model 
 * The 'M' in MVC.
 */
class AN_Model
{
	private $_data;

	function __construct($data = array())
	{
		$this->_data = $data;
	}

	function __get($key)
	{
		return (isset($this->_data[$key])) ? $this->_data[$key] : null;
	}

	function __set($key, $value)
	{
		$this->_data[$key] = $value;
	}

	function __toString()
	{
		return print_r($this->_data, true);
	}

	/*
	 * Method: defineModel
	 * Defines a model at runtime allowing for models to be used without the need to create a class for each one.
	 * 
	 * @param string $name Name for the new model.
	 * @param string $parent The class the model should inherit from. (Default: AN_Model)
	 * @static
	 * @access public
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

	/**
	 * Executes a (prepared) query and returns an array of models. Substitutes '#table' in the query with the name of the table.  Any arguments beyond $query are substituted into the query.
	 *
	 * <code>
	 * <?php $user = User::query('SELECT * FROM #table WHERE id=?', 10); ?>
	 * </code>
	 * 
	 * @param string $class Class of the model.
	 * @param string $query SQL query.
	 * @static
	 * @access public
	 * @return bool|array False if query was not successfully executed, otherwise models from query.
	 */
	static function query($class, $query)
	{
		$query = str_ireplace('#table', Acorn::tableize($class), $query);

		$db = Acorn::database();

		$args = func_get_args();
		array_shift($args);
		$args[0] = $query;

		$res = call_user_func_array(array($db, 'query'), $args);

		if ($res !== false)
		{
			return new AN_Models($class, $res);
		}

		return false;
	}

	/**
	 * Creates a row in the database with $data and returns the new model.
	 *
	 * <code>
	 * <?php $user = User::create(array('name' => 'Skrat', 'email' => 'skrat19@gmail.com')); ?>
	 * </code>
	 * 
	 * @param string $class Class of the model.
	 * @param array $data Data to be saved.
	 * @static
	 * @access public
	 * @return bool|object False if data was unsuccessfully saved, otherwise newly created model.
	 */
	static function create($class, $data)
	{
	}

	/**
	 * Updates row based on given condition(s). 
	 *
	 * <code>
	 * <?php User::update(array('name' => 'Skrat', 'email' => 'skrat19@gmail.com'), 'id = ?', 10); ?>
	 * </code>
	 * 
	 * @param string $class Class of the model.
	 * @param array $value Value(s) to update.
	 * @param string $condition Condition(s) to limit the update to.
	 * @static
	 * @access public
	 * @return bool False if data was unsuccessfully updated, otherwise true.
	 */
	static function update($class, $value, $condition)
	{
	}

	/**
	 * Delete row(s) with given condition(s). 
	 *
	 * <code>
	 * <?php User::delete('id = ?', 10); ?>
	 * </code>
	 * 
	 * @param string $class Class of the model.
	 * @param string $condition Condition(s) to limit the delete to.
	 * @static
	 * @access public
	 * @return bool True if row(s) were deleted.
	 */
	static function delete($class, $condition)
	{
	}

	static function _loadedModel($model)
	{
		$code = <<<EOD
class {$model} extends {$model}Model
{
	static function query()
	{
		\$args = func_get_args();
		array_unshift(\$args, '{$model}');
		return call_user_func_array(array('{$model}Model', 'query'), \$args);
	}
}
EOD;
		eval($code);
	}
}


class AN_Models extends AN_DatabaseResult
{
	private $model;

	function __construct($class, $result)
	{
		if (is_a($result, 'AN_DatabaseResult'))
		{
			parent::__construct($result->stmt);
		}
		else
		{
			parent::__construct($result);
		}

		$this->model = $class;
	}

	function offsetGet($index)
	{
		$res = parent::offsetGet($index);

		if (empty($res))
		{
			return null;
		}
		else
		{
			$class = $this->model;

			return new $class($res);
		}
	}
}

?>
<?php

class AN_Database
{
	private $db;

	function __construct($db_info)
	{
		try
		{
			$pdo = new PDO("{$db_info['adapter']}:host={$db_info['host']};dbname={$db_info['database']}", $db_info['user'], $db_info['password']);

			$this->db = $pdo;
		}
		catch (PDOException $e)
		{
			echo $e->getMessage();
		}
	}

	function query($query)
	{
		$stmt = $this->db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

		$args = func_get_args();
		array_shift($args);

		$num = 1;
		foreach ($args as $arg)
		{
			if ($arg === null)
			{
				$type = PDO::PARAM_NULL;
			}
			else if (is_bool($arg))
			{
				$type = PDO::PARAM_BOOL;
			}
			else if (is_numeric($arg))
			{
				$type = PDO::PARAM_INT;
			}
			else
			{
				$type = PDO::PARAM_STR;
			}

			$stmt->bindValue($num, $arg, $type);
			$num++;
		}

		if ($stmt->execute())
		{
			return new AN_DatabaseResult($stmt);
		}

		return false;
	}
}

class AN_DatabaseResult extends ArrayObject
{
	protected $stmt;

	function __construct($stmt)
	{
		$this->stmt = $stmt;
	}

	function offsetExists($index)
	{
		if (is_int($index))
		{
			return ($index >= 0 && $index < $this->stmt->rowCount());
		}

		return false;
	}

	function offsetGet($index)
	{
		if (is_int($index))
		{
			return $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $index);
		}

		return null;
	}

	function getIterator()
	{
		return new AN_DatabaseResultIterator($this);
	}

	function count()
	{
		return $this->stmt->rowCount();
	}

	function all()
	{
		return $this->stmt->fetchAll();
	}
}

class AN_DatabaseResultIterator extends ArrayIterator
{
	private $r;
	private $index;

	function __construct($stmt)
	{
		$this->r = $stmt;
		$this->index = 0;
	}

	function offsetExists($index)
	{
		return $this->r->offsetExists($index);
	}

	function offsetGet($index)
	{
		return $this->r->offsetGet($index);
	}

	function count()
	{
		return $this->r->count();
	}

	function valid()
	{
		return ($this->index < $this->count());
	}

	function current()
	{
		return $this->offsetGet($this->index);
	}

	function next()
	{
		$this->index++;
	}

	function rewind()
	{
		$this->index = 0;
	}
}

?>
