<?php

class AN_Stream
{
	var $file;

	static function stream_path($path)
	{
		$url = parse_url($path);

		if (empty($url['scheme']))
		{
			return $path;
		}
		
		return (isset($url['path'])) ? $url['host'].$url['path'] : $url['host'];
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
}

// Use the PHP short tags (e.g. <? or <?=) even if it's turned off by including/requiring a file with the "anview" protocol (e.g. include('anview://myfile.php'))
// There is very little overhead, not noticeable (actually was faster than long tags in my minimal tests).
class AN_ViewStream extends AN_Stream
{
	function stream_open($path, $mode, $options, &$opened_path)
	{
		$path = self::stream_path($path);
		$cache = Acorn::$cache_path.'/view_'.basename($path);

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
		$path = self::stream_path($path);
		$cache = Acorn::$cache_path.'/model_'.basename($path);

		if (file_exists($cache) === false || filemtime($path) > filemtime($cache))
		{
			$model_data = file_get_contents($path);
			$new_funcs = "";

			$functions = array(
				'query' => 'static function query() { $args = func_get_args(); array_unshift($args, __CLASS__); call_user_func_array(array("parent", "query"), $args); }'
			);

			foreach ($functions as $name => $def)
			{
				if (stripos("static function {$name}(", $model_data) === false)
				{
					$new_funcs .= $def;
				}

				$model_data = str_ireplace("parent::{$name}(", "parent::{$name}(__CLASS__,", $model_data);
			}

			$model_data = preg_replace('/class (.*?) extends AN_Model\s*?{/', "class \$1 extends AN_Model\n{\n{$new_funcs}", $model_data);
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

