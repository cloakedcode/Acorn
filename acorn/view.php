<?php

/**
 * Renders views and partials within layouts.
 * 
 * @package Acorn
 * @version $id$
 * @author Alan Smith <skrat19@gmail.com> 
 */
class AN_View
{
	/**
	 * Variables to use in the view (e.g. array('msg' => 'User created successfully.')).
	 * 
	 * @static
	 * @access public
	 * @var array
	 */
	static public $vars = array();

	/**
	 * Contents of the rendered view to display within the layout.
	 * 
	 * @static
	 * @access public
	 * @var string
	 */
	static public $view_contents;

	/**
	 * Renders a view with the specified name.
	 * 
	 * @param string $name Name of the view file (e.g. 'users/info' or 'add').
	 * @param string $layout Name of the layout to render the view in. If null no layout will be used. (Default: layout)
	 * @static
	 * @access public
	 */
	static function renderView($name, $layout = 'layout')
	{
	}

	/**
	 * Renders a partial with the specified name and returns it as a string. 
	 * 
	 * @param string $name Name of the partial to render (e.g. 'posts/detail' – would be 'posts/_detail.phtml' on disk).
	 * @param mixed $var 
	 * @param array $extra_vars Variables for the partial to use. (e.g. array('user' => $user, 'time' => time()))
	 * @static
	 * @access public
	 * @return string Contents of the rendered partial.
	 */
	static function renderPartial($name, $var, $extra_vars = array())
	{
	}

	/**
	 * Renders the file at the specified path.
	 * 
	 * @param string $path Path of the file.
	 * @param string $layout Name of the layout to render the view in. If null no layout will be used. (Default: layout)
	 * @static
	 * @access public
	 */
	static function renderFile($path, $layout = 'layout')
	{
	}
}

?>
