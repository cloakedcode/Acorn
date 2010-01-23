<?php

class AN_Database
{
	private $db;

	function __construct($db_info)
	{
		try
		{
			$pdo = new PDO("{$db_info['adapter']}:host={$db_info['host']};dbname={$db_info['database']}", $db_info['user'], $db_info['password']);

			$this->db = $pdo;
		}
		catch (PDOException $e)
		{
			echo $e->getMessage();
		}
	}

	function query($query)
	{
		$stmt = $this->db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

		$args = func_get_args();
		array_shift($args);

		$num = 1;
		foreach ($args as $arg)
		{
			if ($arg === null)
			{
				$type = PDO::PARAM_NULL;
			}
			else if (is_bool($arg))
			{
				$type = PDO::PARAM_BOOL;
			}
			else if (is_numeric($arg))
			{
				$type = PDO::PARAM_INT;
			}
			else
			{
				$type = PDO::PARAM_STR;
			}

			$stmt->bindValue($num, $arg, $type);
			$num++;
		}

		if ($stmt->execute())
		{
			return new AN_DatabaseResult($stmt);
		}

		return false;
	}
}

class AN_DatabaseResult extends ArrayObject
{
	protected $stmt;

	function __construct($stmt)
	{
		$this->stmt = $stmt;
	}

	function offsetExists($index)
	{
		if (is_int($index))
		{
			return ($index >= 0 && $index < $this->stmt->rowCount());
		}

		return false;
	}

	function offsetGet($index)
	{
		if (is_int($index))
		{
			return $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $index);
		}

		return null;
	}

	function getIterator()
	{
		return new AN_DatabaseResultIterator($this);
	}

	function count()
	{
		return $this->stmt->rowCount();
	}

	function all()
	{
		return $this->stmt->fetchAll();
	}
}

class AN_DatabaseResultIterator extends ArrayIterator
{
	private $r;
	private $index;

	function __construct($stmt)
	{
		$this->r = $stmt;
		$this->index = 0;
	}

	function offsetExists($index)
	{
		return $this->r->offsetExists($index);
	}

	function offsetGet($index)
	{
		return $this->r->offsetGet($index);
	}

	function count()
	{
		return $this->r->count();
	}

	function valid()
	{
		return ($this->index < $this->count());
	}

	function current()
	{
		return $this->offsetGet($this->index);
	}

	function next()
	{
		$this->index++;
	}

	function rewind()
	{
		$this->index = 0;
	}
}

?>
