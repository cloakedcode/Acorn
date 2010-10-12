<?php

define('IS_PHP_5_3', (float)phpversion() >= 5.3);

class AN_Event
{
	static private $callbacks = array();

	static function addCallback($event, $callback)
	{
		return self::addCallbackBefore($event, $callback, null);
	}

	static function addCallbackBefore($event, $callback, $other_callback)
	{
		if (is_string($callback) === false && (is_array($callback) && count($callback) < 2))
		{
			throw new Exception('Invalid callback.');
		}

		if (isset(self::$callbacks[$event]) === false)
		{
			self::$callbacks[$event] = array();
		}
		
		$index = array_search($other_callback, self::$callbacks[$event]);

		if ($index !== false)
		{
			if ($index !== 0)
			{
				self::$callbacks[$event][$index -1] = $callback;
			}
			else
			{
				array_unshift(self::$callback[$event], $callback);
			}
			
			return true;
		}
		else
		{
			array_unshift(self::$callbacks[$event], $callback);

			return true;
		}
			
		return false;
	}

	static function removeCallback($event, $callback)
	{
		if (empty(self::$callbacks[$event]))
		{
			return true;
		}

		$index = array_search($callback, self::$callbacks[$event]);

		if ($index !== false)
		{
			unset(self::$callbacks[$event][$index]);
			
			return true;
		}

		return false;
	}

	static function run($event, $data = null)
	{
		$callbacks = (isset(self::$callbacks[$event])) ? self::$callbacks[$event] : false;

		if (empty($callbacks) === false)
		{
			foreach ($callbacks as $func)
			{
				$return_value = call_user_func($func, $data);

				if ($return_value === false)
				{
					return false;
				}
			}
		}

		return true;
	}
	
	static function runFilterOnData($event, &$data)
	{
		$callbacks = (isset(self::$callbacks[$event])) ? self::$callbacks[$event] : false;

		if (empty($callbacks))
		{
			return false;
		}

		$orig_data = $data;
		foreach ($callbacks as $func)
		{
			$return_value = call_user_func_array($func, array(&$data));

			if ($return_value === false)
			{
				if ($data !== $orig_data)
				{
					return true;
				}

				return false;
			}
		}

		return false;
	}
}

?>
