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
		$model = (substr($name, -5) === 'Model') ? substr($name, 0, -5) : $name;
		$code = <<<EOD
class {$name} extends {$parent}
{
	static function query()
	{
		\$args = func_get_args();
		array_unshift(\$args, '{$model}');
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

		if (empty($db) === false)
		{
			$args = func_get_args();
			array_shift($args);
			$args[0] = $query;

			$res = call_user_func_array(array($db, 'query'), $args);

			if ($res !== false)
			{
				return new AN_Models($class, $res);
			}
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
		else if (is_a($res, $this->model) === false)
		{
			$class = $this->model;

			$res = new $class($res);
			$this->offsetSet($index, $res);
		}

		return $res;
	}
}

?>
