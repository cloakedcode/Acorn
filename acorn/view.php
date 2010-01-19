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
	 * Variables to use in the view.
	 *
	 * <code>
	 * <?php AN_View::$vars = array('msg' => 'User created successfully.'); ?>
	 * </code>
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
	 * <code>
	 * <?php AN_View::renderView('users/info'); ?>
	 * </code>
	 * 
	 * @param string $name Name of the view file.
	 * @param string $layout Name of the layout to render the view in. If null no layout will be used. (Default: layout)
	 * @static
	 * @access public
	 */
	static function renderView($name, $layout = 'layout')
	{
		$__path = Acorn::filePath('view', $name);

		if ($__path !== false)
		{
			$__layout = $layout;
			extract((array)self::$vars, EXTR_OVERWRITE);

			ob_start();
			include($__path);
			self::$view_contents = ob_get_clean();

			$__layout_path = Acorn::filePath('layout', $__layout);

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
		$dir = dirname($name);
		$name = basename($name);
		$file = (empty($dir)) ? '_'.$name : $dir.'/_'.$name;

		$__path = Acorn::filePath('view', $file);

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
