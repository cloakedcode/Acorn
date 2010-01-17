<?php

class AN_Controller
{
	/**
	 * Layout to use when rendering. 
	 * 
	 * @var string
	 * @access public
	 */
	public $layout = 'layout';

	/**
	 * Whether or not to render. If set to false calls to renderView and renderPartial will not output anything. 
	 * 
	 * @var bool
	 * @access public
	 */
	public $should_render = true;

	/**
	 * Renders a view. If no slash is found in the name, the controller's name is prefixed to the name (e.g. 'detail' becomes 'users/detail').
	 * 
	 * @param string $name
	 * @see View::renderView
	 * @access public
	 */
	function renderView($name)
	{
		if ($this->should_render === false)
		{
			return;
		}

		if (strpos($name, '/') === false)
		{
			$name = $this->_name()."/{$name}";
		}

		AN_View::$vars = $this;

		AN_View::renderView($name, $this->layout);
	}

	/**
	 * Renders a partial and returns the contents.
	 * 
	 * @param string $name 
	 * @param mixed $var 
	 * @param array $extra_vars 
	 * @see View::renderPartial
	 * @access public
	 * @return Contents of rendered partial.
	 */
	function renderPartial($name, $var, $extra_vars = array())
	{
	}

	private function _name()
	{
		return strtolower(substr(get_class($this), 0, -10));
	}
}

?>

