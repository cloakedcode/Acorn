<?php

class AN_Stream
{
	var $file;

	static function stream_path($path)
	{
		$bits = (strpos($path, '://')) ? explode('://', $path) : array();

		if (empty($bits))
		{
			return $path;
		}
		
		return $bits[1];
	}

	function stream_open($path, $mode, $options, &$opened_path)
	{
		$path = self::stream_path($path);

		$this->file = fopen($path, $mode);

		if ($this->file)
		{
			$opened_path = $path;

			return true;
		}

		return false;
	}

	function stream_close()
	{
		fclose($this->file);
	}

	function stream_read($count)
	{
		return fread($this->file, $count);
	}

	function stream_tell()
	{
		return ftell($this->file);
	}

	function stream_eof()
	{
		return feof($this->file);
	}

	function stream_seek($offset, $whence)
	{
		return fseek($this->file, $offset, $whence);
	}

	function stream_stat()
	{
		return fstat($this->file);
	}

        function cache_path($filename)
        {
          $path = Acorn::$cache_path;

          if (is_dir($path) === false)
          {
            mkdir($path);
          }

          return $path.'/'.$filename;
        }
}

// Use the PHP short tags (e.g. <? or <?=) even if it's turned off by including/requiring a file with the "anview" protocol (e.g. include('anview://myfile.php'))
// There is very little overhead, not noticeable (actually was faster than long tags in my minimal tests).
class AN_ViewStream extends AN_Stream
{
	function stream_open($path, $mode, $options, &$opened_path)
	{
		$cache = self::cache_path('view_'.md5($path));
		$path = self::stream_path($path);

		if (file_exists($cache) === false || filemtime($path) > filemtime($cache))
		{
			$view_data = file_get_contents($path);

			if (ini_get('short_open_tag') == false)
			{
				$view_data = preg_replace(array('/<\?(?!php)/i', '/<\?php=/i'), array('<?php', '<?php echo '), $view_data);
			}

			file_put_contents($cache, $view_data);
		}

		if (parent::stream_open($cache, $mode, $options, $opened_path))
		{
			$opened_path = $path;

			return true;
		}

		return false;
	}
}

class AN_ModelStream extends AN_Stream
{
	function stream_open($path, $mode, $options, &$opened_path)
	{
		$cache = self::cache_path('model_'.md5($path));
		$path = self::stream_path($path);

		if (file_exists($cache) === false || filemtime($path) > filemtime($cache))
		{
			$model_data = file_get_contents($path);
			$new_funcs = "";
			$extract_class_code = '';

			if (strpos($model_data, ' extends AN_Model'))
			{
				$extract_class_code = '$args[0] = $args[0]["an_model_class"];';
			}

			$functions = array(
				'query' => 'static function query() { $args = func_get_args(); if (is_array($args[0]) === false || empty($args[0]["an_model_class"])) array_unshift($args, array("an_model_class" => __CLASS__)); '.$extract_class_code.' return call_user_func_array(array("parent", "query"), $args); }',
				'create' => 'static function create() { $args = func_get_args(); if (is_array($args[0]) === false || empty($args[0]["an_model_class"])) array_unshift($args, array("an_model_class" => __CLASS__)); '.$extract_class_code.' return call_user_func_array(array("parent", "create"), $args); }',
				'insert' => 'static function insert() { $args = func_get_args(); if (is_array($args[0]) === false || empty($args[0]["an_model_class"])) array_unshift($args, array("an_model_class" => __CLASS__)); '.$extract_class_code.' return call_user_func_array(array("parent", "insert"), $args); }',
				'update' => 'static function update() { $args = func_get_args(); if (is_array($args[0]) === false || empty($args[0]["an_model_class"])) array_unshift($args, array("an_model_class" => __CLASS__)); '.$extract_class_code.' return call_user_func_array(array("parent", "update"), $args); }'
			);

			foreach ($functions as $name => $def)
			{
				if (stripos("static function {$name}(", $model_data) === false)
				{
					$new_funcs .= $def."\n";
				}

				$model_data = str_ireplace("parent::{$name}(", "parent::{$name}(__CLASS__,", $model_data);
			}

			$model_data = preg_replace('/^\}/m', "{$new_funcs}}", $model_data);
			file_put_contents($cache, $model_data);
		}

		parent::stream_open($cache, $mode, $options, $opened_path);

		if ($opened_path == $cache)
		{
			$opened_path = $path;
			
			return true;
		}

		return false;
	}
}

stream_wrapper_register("anview", "AN_ViewStream");
stream_wrapper_register("anmodel", "AN_ModelStream");

?>
