<?php
/**
 * DBLite Adapter interface. You can extends this class to implement your custom database platform adapter.
 *
 * $Id: adapter.php 2339 2011-07-01 08:41:05Z wangjb $
 */
namespace lib\dblite;

abstract class Adapter
{
	/**
	 * Create a PDO instance. It should read $this->_config to create a correct PDO object.
	 *
	 * @return PDO
	 */
	abstract public function createPDO();
	
	/**
	 * Quote method for table name, field name ...
	 *
	 * @return string
	 */
	abstract public function quoteField($field);
	
	/**
	 * Qutoe method for data value in SQL.
	 *
	 * @return string
	 */
	abstract public function quoteValue($value);

	/**
	 * Explain a SELECT query. The adapter must overwrite this method to implement this function for specific database platform.

	 * @param string $sql the SELECT query statement.
	 * @return array the explanation.
	 */
	public function explain($sql)
	{
		return null;
	}

	/**
	 * Get table prefix string
	 *
	 * @deprecated
	 */
	public function getPrefix()
	{
		return '';
	}
	
	/**
	 * Escape the value for special characters(single-quote, double-quotes...)
	 *
	 * @return string the escaped string.
	 */
	abstract public function escapeValue($value);
}
