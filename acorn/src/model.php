<?php

/*
 * Class: AN_Model 
 * The 'M' in MVC.
 */
class AN_Model
{
	private $_data;
	private $_changed_data;

	static private $_table_defs = array();

	
	public $errors = array();
	protected $validation_rules = array();

	function __construct($data = array())
	{
		$this->_data = $data;
		$this->_changed_data = $data;
	}

	function __get($key)
	{
		if ($key === 'primary_key')
		{
			$class = get_class($this);

			self::_tableDefinition($class);
			return self::$_table_defs[$class]['primary_key'];
		}

		return (isset($this->_data[$key])) ? $this->_data[$key] : null;
	}

	function __set($key, $value)
	{
		if (isset($this->_data[$key]) === false || $this->_data[$key] != $value)
		{
			$this->_data[$key] = $value;
			$this->_changed_data[$key] = $value;
		}
	}

	function __isset($key)
	{
		return isset($this->_data[$key]);
	}

	function __unset($key)
	{
		unset($this->_data[$key]);
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

	/*
         * Method: query
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

	/*
         * Method: create
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
	 * @return object The model that was used to save the data. If the 'errors' property is empty the save was successful.
	 */
	static function create($class, $data)
	{
		$model = new $class($data);
		$model->save();

		return $model;
	}

	static function insert($class, $data)
	{
		$query = "INSERT INTO #table SET";
		$values = array();
		$columns = self::_tableDefinition($class);

		foreach ($data as $key => $val)
		{
			if (isset($columns[$key]))
			{
				$query .= " `{$key}` = ?, ";
				$values[] = $val;
			}
		}
		
		array_unshift($values, rtrim($query, ', '));
		array_unshift($values, $class);
		if (call_user_func_array(array('self', 'query'), $values))
		{
			return Acorn::database()->lastInsertID();
		}

		return false;
	}

	/*
         * Method: update
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
		$query = "UPDATE #table SET ";
		$vals = array();
		$columns = self::_tableDefinition($class);

		foreach ($value as $key => $val)
		{
			if (isset($columns[$key]))
			{
				$query .= "`{$key}` = ?, ";
				$vals[] = $val;
			}
		}

		$query = rtrim($query, ', ')." WHERE {$condition}";

		$args = func_get_args();
		$args = array_merge($vals, array_slice($args, 3));
		array_unshift($args, $query);
		array_unshift($args, $class);

		return call_user_func_array(array('self', 'query'), $args);
	}

	/*
         * Method: delete
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

	function save($validate = true)
	{
		if ($validate)
		{
			$this->errors = array();
			$this->validate();
			if (empty($this->errors) === false)
			{
				return false;
			}
		}

		$class = get_class($this);
		$table_def = self::_tableDefinition($class);
		
		if (isset($this->_data[$this->primary_key]))
		{
			$key = $this->primary_key;
			return self::update($class, $this->_changed_data, "{$key} = ?", $this->_data[$key]);
		}
		else
		{
			$insert = self::insert($class, $this->_data);

			if ($insert !== false && $insert !== true && isset($table_def['id']))
			{
				$this->id = $insert;
				return true;
			}

			return $insert;
		}
	}

	function validate()
	{
		$def = $this->_tableDefinition(get_class($this));
		$rules = array_merge($def, $this->validation_rules);
		
		$changed_data = empty($this->_changed_data);
		$primary_key = $this->primary_key;

		foreach ($rules as $column => $rule)
		{
			if (($changed_data && isset($this->_changed_data[$column]) === false) || $column === $primary_key || $column === 'primary_key')
			{
				continue;
			}

			$errors = array();
			$val = $this->{$column};

			if (isset($rule['length']) && strlen((string)$val) > $rule['length'])
			{
				$errors[] = Acorn::camelize($column)." is too long.";
			}

			if (isset($rule['null']) && $rule['null'] === false && (empty($val) && $val !== 0))
			{
				$errors[] = Acorn::camelize($column)." is required.";
			}

			if (empty($errors) === false)
			{
				$this->errors[$column] = $errors;
			}
		}
	}

	function column_definition($column)
	{
		$def = self::_tableDefinition(get_class($this));

		return (empty($def[$column])) ? null : $def[$column];
	}

	static function _tableDefinition($class)
	{
		if (empty(self::$_table_defs[$class]))
		{
			$table = Acorn::tableize($class);
			$defs = self::query($class, "DESCRIBE `{$table}`");

			foreach ($defs as $def)
			{
				$len = null;

				if (preg_match('/([^\(]*)\((\d+)\)/', $def->Type, $matches))
				{
					$type = $matches[1];
					$len = $matches[2];
				}
				else
				{
					$type = $def->Type;
				}

				$pretty_def = array('type' => $type, 'null' => ($def->Null === "YES"));

				if (isset($len))
				{
					$pretty_def['length'] = (int)$len;
				}

				self::$_table_defs[$class][$def->Field] = $pretty_def;

				if ($def->Key == "PRI")
				{
					self::$_table_defs[$class]['primary_key'] = $def->Field;
				}
			}
		}

		return self::$_table_defs[$class];
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

		if (is_array($res) && array_filter($res) == array())
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
