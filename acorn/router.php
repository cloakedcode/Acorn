<?php

class AN_Router
{
	private $routes = array();

	function __construct($file)
	{
		include($file);

		$this->routes = $routes;
	}

	function urlToParams($url)
	{
		$params = array();
		
		foreach ($this->routes as $r)
		{
			if (preg_match($r, $url, $params))
			{
				break;
			}
		}

		// Remove all but named entries
		if (empty($params) === false)
		{
			foreach ($params as $key => $val)
			{
				if (is_int($key))
				{
					unset($params[$key]);
				}
			}
		}

		return $params;
	}
}

?>
