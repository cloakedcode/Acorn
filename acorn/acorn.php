<?php

class Acorn
{
	/**
	 * Loads file named $name.
	 * 
	 * @param string $type Type of file (e.g. 'controller' or 'model').
	 * @param string $name Name of file (e.g. 'Application' or 'User').
	 * @static
	 * @see filePath
	 * @access public
	 * @return bool True if successfully loaded file.
	 */
	static function load($type, $name)
	{
	}

	/**
	 * Searches Acorn::config('include_paths') for the specified file. 
	 * 
	 * @param string $type Type of file (e.g. 'controller' or 'model').
	 * @param string $name Name of file (e.g. 'Application' or 'User').
	 * @static
	 * @access public
	 * @return string|false False if no file exists, otherwise path of file.
	 */
	static function filePath($type, $name)
	{
	}
}

?>
