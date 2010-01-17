<?php

class AN_Model
{
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
	 * @return array Models as result of executing the query.
	 */
	static function query($class, $query)
	{
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
}

?>
