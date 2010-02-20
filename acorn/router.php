<?php

class AN_Router
{
	private $_routes = array();
	private $_named_connections = array();
	private $_cached_time;
	
	function __construct()
	{
		if (file_exists(ROOT_DIR.'/cache/routes.php'))
		{
			include(ROOT_DIR.'/cache/routes.php');

			$this->_named_connections = $named_connections;
			$this->_routes = $routes;
			$this->_cached_time = $cached_time;
		}
		else
		{
			static $loading = false;

			if ($loading === false)
			{
				$loading = true;
				$router = new AN_Router();

				include(ROOT_DIR.'/config/routes.php');

				$this->_named_connections = $router->_named_connections;
				$this->_routes = $router->_routes;
			}
		}

		/*
		$funcs = '';
		
		foreach ($this->_named_connections as $connection)
		{
			$funcs .= $connection;
		}
		
		file_put_contents($cached_routes_path, '<?php $routes = '.var_export($this->_routes, true).';'.$funcs.'?>');
		*/
	}

	function __destruct()
	{
		$cache_file = ROOT_DIR.'/cache/routes.php';
		if (Acorn::config('env') === 'debug' || empty($this->_cached_time) || file_exists($cache_file) === false || $this->_cached_time < filemtime(ROOT_DIR.'/config/routes.php'))
		{
			$time = (file_exists($cache_file) ? filemtime($cache_file) : 0);
			$code = "<?php\n";
			$code .= '$named_connections = '.var_export($this->_named_connections, true).";\n";
			$code .= '$routes = '.var_export($this->_routes, true).";\n";
			$code .= '$cached_time = '.var_export($time, true).";\n";
			$code .= '?>';

			file_put_contents($cache_file, $code);
		}
	}
	
	function __call($func, $args)
	{
		$this->_named_connection($func, $args[0], (isset($args[1]) ? $args[1] : array()), (isset($args[2]) ? $args[2] : array()));
	}
	
	function root($options = array(), $requirements = array())
	{
		$this->connect('', $options, $requirements);
	}
	
	function connect($url, $options = array(), $requirements = array())
	{		
		$options = (is_string($options) ? $this->_paramsFromString($options) : $options);

		$route = array('url' => $url, 'options' => $options);
		$url_regex = '';
		
		if (preg_match_all('/([:|\*]{1})(\w*)/', $url, $matches))
		{
			$url_regex = preg_replace('/[\[\\\^\$\.\|\?\*\+\(\)\{\}]/', '\$1', $url);
			$match_count = count($matches[2]);
			
			for ($i = 0; $i < $match_count; $i++)
			{
				$spec = $matches[1][$i];
				$name = $matches[2][$i];
				$key = $spec . $name;
				
				if ($spec === ':')
				{
					$route['key_names'][] = $name;
					$regex = (isset($requirements[$name])) ? $requirements[$name] : '[^/;,\.\?]*';
				}
				else if ($spec === '*')
				{
					$regex = '.*';
				}
				
				if (isset($options[$name]))
				{
					$regex = "?(?<{$name}>{$regex})";
				}
				else
				{				
					$regex = "(?<{$name}>{$regex})";
				}

				$url_regex = str_replace($key, $regex, $url_regex);
				$route['specs'][$name] = $spec;
			}
		}
		
		$route['url_regex'] = "|^{$url_regex}\$|";
		$this->_routes[$url] = $route;
		
		return $route;
	}
	
	function paramsToURL($params)
	{
		$globals = Acorn::$params;
		$params = (is_string($params) ? $this->_paramsFromString($params) : $params);
		
		foreach ($this->_routes as $route_url => $route)
		{
			if ($route_url !== '')
			{
				$options = $route['options'];

				if (isset($params['controller_path']) && $params['controller_path'] !== false)
				{
					if (empty($options['controller_path']) || $options['controller_path'] !== $params['controller_path'])
					{
						continue;
					}
				}
				else if (isset($options['controller_path']))
				{
					continue;
				}

				$use_defaults = true;
				$url = $route['url'];
				$filled_keys = array();
				
				foreach ($route['key_names'] as $key)
				{
					if (isset($params[$key]))
					{
						$value = $params[$key];
						$use_defaults = false;
					}
					else if ($use_defaults)
					{
						if (isset($globals[$key]))
						{
							$value = $globals[$key];
						}
						else if (isset($options[$key]))
						{
							$value = $options[$key];
						}
						else
						{
							break;
						}
					}
					else
					{
						break;
					}
					
					$url = str_replace($route['specs'][$key].$key, $value, $url);
					$filled_keys[] = $key;
				}

				if ($filled_keys == $route['key_names'])
				{
					if (isset($params['controller_path']))
					{
						$filled_keys[] = 'controller_path';
					}
					$filled_keys[] = 'controller';
					$filled_keys[] = 'action';

					$diff = array_diff(array_keys($params), $filled_keys);
					
					if (empty($diff))
					{
						return $url;
					}
				}
			}
		}
		
		return null;
	}
	
	function urlToParams($url)
	{
		$url = trim($url, '/');

		if (empty($url))
		{
			return $this->_routes['']['options'];
		}
		
		foreach ($this->_routes as $route_url => $route)
		{
			if ($route_url !== '')
			{
				$params = $this->_route_params_from_url($route, $url);
				
				if (array_keys($params) == $route['key_names'])
				{
					return (isset($route['options'])) ? array_merge($route['options'], $params) : $params;
				}
			}
		}
		
		return array();
	}
	
	private function _named_connection($name, $url, $options = array(), $requirements = array())
	{
		$func_name = $name . '_url';
		
		if (function_exists($func_name) === false)
		{
			$func = "
			function $func_name(\$params = ".var_export($options, true).")
			{
				\$url = '$url';
				foreach (Acorn::\$params as \$key => \$value)
				{
					if (is_array(\$value) === false && isset(\$params[\$key]) === false)
					{
						\$params[\$key] = \$value;
					}
				}
				foreach (\$params as \$key => \$value)
				{
					\$url = str_replace(\":\$key\", \$value, \$url);
				}
				return ACORN_URL . '/' . \$url;
			}";
			
			array_push($this->_named_connections, $func);
			
			eval($func);
		}
		
		$this->connect($url, $options, $requirements);
	}

	private function _route_params_from_url($route, $url)
	{
		$params = array();
		preg_match($route['url_regex'], $url, $matches);
		
		if (empty($matches))
		{
			return array();
		}

		array_shift($matches);
		
		foreach ($route['key_names'] as $key_name)
		{
			if (empty($matches[$key_name]) === false)
			{
				if ($route['specs'][$key_name] === '*')
				{
					$params[$key_name] = explode('/', $matches[$key_name]);
				}
				else
				{
					$params[$key_name] = $matches[$key_name];
				}
			}
			else if (empty($matches[$key_name]) && isset($route['options'][$key_name]))
			{
				$params[$key_name] = $route['options'][$key_name];
			}
		}
		
		return $params;
	}

	private function _paramsFromString($str)
	{
		$bits = explode('#', $str);
		$params = array();

		if (empty($bits[0]) || empty($bits[1]))
		{
			if (empty($bits[0]))
			{
				$params['action'] = $bits[1];
			}
			else
			{
				$params['controller'] = $bits[0];
			}
		}
		else
		{
			$params['controller'] = $bits[0];
			$params['action'] = $bits[1];
		}
		return $params;
	}
}

?>
