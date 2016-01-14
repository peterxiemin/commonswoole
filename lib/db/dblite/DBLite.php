<?php
/**
 * Created by PhpStorm.
 * User: haohaizihcc
 * Date: 2016/01/11
 * Time: 14:00
 * Description: 数据库操作类,对pdo的封装，使用了适配器模式
 */
namespace lib\db\dblite;

class DBLite
{
	private $_lastResult = array();
	private $_lastStatement;
	private $_pdo;
	private $_timer = array();
	protected $_adapter;
	protected $_prefix;
	private $_config;

	/**
	 * @param $config array 数据库的完整配置文件
	 * @param $id string 具体要连接的数据库的标志
	 */
	public function __construct($config, $id = null)
	{
		if(!extension_loaded('pdo'))
		{
			throw new \lib\db\dblite\Exception('PDO extension is not loaded.');
		}
		if (!isset($config['DBLite']['adapter']))
		{
			throw new \lib\db\dblite\Exception('config error:lack adapter');
		}
		$this->_config = $config;
		$adapter = $this->_config['DBLite']['adapter'];
		if (!isset($this->_config[$adapter]))
		{
			throw new \lib\db\dblite\Exception('config error:lack adapter config');
		}
		if (empty($id))
		{
			$id = isset($this->_config['DBLite']['defaultConnection']) ? $this->_config['DBLite']['defaultConnection'] : '';
		}
		$adpaterName = '\\lib\\dblite\\adapter\\' . $adapter;
		$this->_adapter = new $adpaterName($this->_config[$adapter], $id);
		$this->_prefix = $this->_adapter->getPrefix();
	}
	/**
	 * Set prefix
	 *
	 * @param string $prefix Prefix
	 */
	public function setPrefix($prefix)
	{
		$this->_prefix = $prefix;
	}

	/**
	 * Get prefix
	 *
	 * @return string $prefix Prefix
	 */
	public function getPrefix()
	{
		return $this->_prefix;
	}


	/**
	 * start a transaction
	 *
	 * @return void
	 */
	public function transaction()
	{
		if(!isset($this->_pdo))
			$this->_creatPDO();
		$this->_pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
		$this->_pdo->beginTransaction();
	}

	/**
	 * commit a transaction
	 *
	 * @return void
	 */
	public function commit()
	{
		if(!isset($this->_pdo))
			$this->_creatPDO();
		$this->_pdo->commit();
		$this->_pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
	}

	/**
	 * rollback a transaction
	 *
	 * @return void
	 */
	public function rollback()
	{
		if(!isset($this->_pdo))
			$this->_creatPDO();
		$this->_pdo->rollBack();

		$this->_pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
	}


	private function _creatPDO() {
		$this->_pdo = $this->_adapter->createPDO();
		$this->_pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
		$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	private function _getRealSQL($sql, $bind)
	{
		$keys = $values = array();
		foreach($bind as $key => $value)
		{
			$keys[] = "$key";
			$values[] = $this->_adapter->quoteValue($this->_adapter->escapeValue($value));
		}

		$realSQL = str_replace($keys, $values, $sql);
		$realSQL = trim($realSQL);
		return $realSQL;
	}

	/*
	 * Get an array which contains SQL statement and the time it takes. Also contains 'EXPLAIN' infomation for SELECT statements if your turn on the Debug module.

	 * @param string $sql the SQL statement, without bound parameters
	 * @param string $time the time that is returned by _endTimer();
	 * @return array The SQL statistics information array, including 'sql', 'time', and 'explain'.
	 **/
	private function _getSQLStats($sql, $time)
	{
		$stats = array(
			'sql' => $sql,
			'time' =>$time,
		);
		if($this->_config['DBLite']['debug'] && strtoupper(substr($sql, 0, 6)) == 'SELECT')
		{
			$stats['explain'] = $this->_adapter->explain($sql);
		}

		return $stats;
	}

	/**
	 * Execute 'read' SQL query, like SELECT and SHOW statements. Make sure the SQL is only read.
	 *
	 * @param string $sql The SQL Statement to be executed.
	 * @param array $bind If the SQL is a prepared statement, like "SELECT * FROM users WHERE name = :name", where ":name" is a placeholder, you can pass "array(':name' => 'bob')" to the second $bind parameter. It means "SELECT * FROM users WHERE name = 'bob'". Prepared statements can defend SQL injection.
	 * @return PDOStatement
	 */
	public function query($sql, array $bind = array())
	{
		$this->_creatPDO();
		$succ = true;

		// Prepared SQL Statements.
		$sth = $this->_pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
		$this->_beginTimer();
		$succ = $sth->execute($bind);

		$time = $this->_endTimer();
		$sql = $this->_getRealSQL($sql, $bind);
		$history[] = $this->_getSQLStats($sql, $time);

		if(!$succ)
		{
			$errorInfo = $sth->errorInfo();
			throw new Exception("Failed to execute query statement: :sql\nError Code: :errcode\nError Info: :errinfo\n", array(':sql' => $sql, ':errcode' => $sth->errorCode(), ':errinfo' => $errorInfo[2]));
		}
		$this->_lastStatement = $sth;
		return $this->_lastStatement;
	}

	/**
	 * Execute the 'modify' SQL, like INSERT, UPDATE, DELETE, ALTER, DROP...
	 *
	 * @param string $sql The SQL to be executed
	 * @param array $bind See DBLite::query()
	 * @return int the number of rows that were affacted
	 */
	public function execute($sql, array $bind = array())
	{
		$this->_creatPDO();
		$sth = $this->_pdo->prepare($sql);

		$this->_beginTimer();
		$succ = $sth->execute($bind);
		$time = $this->_endTimer();

		$sql = $this->_getRealSQL($sql, $bind);

		$history[] = $this->_getSQLStats($sql, $time);

		if(!$succ)
		{
			$errorInfo = $sth->errorInfo();
			throw new Exception("Failed to execute SQL: :sql\nError Code: :errcode\nError Info: :errinfo\n", array(':sql' => $sql, ':errcode' => $sth->errorCode(), ':errinfo' => $errorInfo[2]));
		}
		$this->_lastStatement = $sth;
		return $sth->rowCount();
	}

	/**
	 * Get results in array form by SQL. Notify: the SQL just for selection. So make sure which function you choose between execute() and getResult().
	 *
	 * @param string $sql The 'read' SQL statement.
	 * @param int $fetchStyle Controls how the next row will be returned to the caller. See http://www.php.net/manual/en/pdostatement.fetch.php.
	 * @return array the result set
	 */
	public function getResult($sql = null, $fetchStyle = null)
	{
		if(!empty($sql))
		{
			$this->query($sql);
		}
		if($fetchStyle === null)
		{
			$fetchStyle = isset($this->_config['fetchStyle']) ? $this->_config['fetchStyle'] : \PDO::FETCH_ASSOC;
		}

		$this->_lastResult = $this->_lastStatement->fetchAll($fetchStyle);
		return $this->_lastResult;
	}

	/**
	 * Get the variable in Column $x, Row $y of the result.
	 *
	 * @param string the 'read' SQL statement.
	 * @param int $x Column number. 0 means the first column.
	 * @param int $y Row number. 0 means the first row.
	 * @return mix the variable.
	 */
	public function getVar($sql = null, $x = 0, $y = 0)
	{
		$this->getResult($sql, \PDO::FETCH_NUM);
		return isset($this->_lastResult[$y][$x]) ? $this->_lastResult[$y][$x] : null;
	}

	/**
	 * Get a specific row from result set.
	 *
	 * @param string $sql The SQL statement to be executed.
	 * @param int $y Row number. 0 means the first row.
	 * @param int $fetchStyle See {@link DBLite::getResult()}.
	 * @return array | object One record.
	 */
	public function getRow($sql = null, $y = 0, $fetchStyle = null)
	{
		if($fetchStyle === null)
		{
			$fetchStyle = isset($this->_config['fetchStyle']) ? $this->_config['fetchStyle'] : \PDO::FETCH_ASSOC;
		}
		$this->getResult($sql, $fetchStyle);
		return empty($this->_lastResult) ? array() : (isset($this->_lastResult[$y]) ? $this->_lastResult[$y] : null);
	}

	/**
	 * Get all the values from a specific column of the result set.
	 *
	 * @param string $sql The SQL statement to be executed.
	 * @param int $x the column number. 0 means the first column/field in result set.
	 * @return array All the values in column $x.
	 */
	public function getCol($sql = null, $x = 0)
	{
		$this->getResult($sql, \PDO::FETCH_NUM);
		$result = array();
		foreach($this->_lastResult as $row)
		{
			$result[] = isset($row[$x]) ? $row[$x] : null;
		}
		return $result;
	}

	/**
	 * Get last insert ID.
	 *
	 * @return mix The last insert ID.
	 */
	public function getInsertId()
	{
		return $this->_pdo->lastInsertId();
	}

	/**
	 * Insert a record to a table.
	 *
	 * @param string $table The table's name
	 * @param array The data to be inserted. Its format is like array('field_1' => 'value_1', 'field_2' => 'value_2', ...).
	 * @return int the number of rows that were affacted
	 */
	public function insert($table, array $data)
	{
		$fields = $values = array();
		foreach($data as $key => $value)
		{
			$value = addslashes($value);
			$fields[] = $this->_adapter->quoteField($key);
			$values[] = $this->_adapter->quoteValue($value);
		}

		return $this->execute("INSERT INTO " . $this->_adapter->quoteField($this->_prefix.$table) . "(" . implode(',', $fields) . ") VALUES(" . implode(',', $values) . ")");
	}

	/**
	 * Replace a record into a table.
	 *
	 * @param string $table The table's name
	 * @param array The data to be replaced. Its format is like array('field_1' => 'value_1', 'field_2' => 'value_2', ...).
	 * @return int the number of rows that were affacted.
	 */
	public function replace($table, array $data)
	{
		$fields = $values = array();
		foreach($data as $key => $value)
		{
			$value = addslashes($value);
			$fields[] = $this->_adapter->quoteField($key);
			$values[] = $this->_adapter->quoteValue($value);
		}
		return $this->execute("REPLACE INTO " . $this->_adapter->quoteField($this->_prefix.$table) . "(" . implode(',', $fields) . ") VALUES(" . implode(',', $values) . ")");
	}

	/**
	 * Update with some new data to a table.
	 *
	 * @param string $table The table's name.
	 * @param array $data The new data values. The format is like array('field_1' => 'value_1, 'field_2' => 'value_2').
	 * @param string $where The SQL where clause. It tells database which records will be updated.
	 * @return int the number of rows that were affacted.
	 */
	public function update($table, array $data, $where)
	{
		$setClause = $this->_implodeFields($data);
		return $this->execute("UPDATE " . $this->_adapter->quoteField($this->_prefix.$table) . " SET $setClause WHERE $where");
	}

	/**
	 * Delete some records from a table. This method contruct SQL like 'DELETE FROM $table WHERE $where'.
	 *
	 * @param string $table The table's name.
	 * @param string $where The SQL where clause.
	 * @return int The number of rows that were deleted.
	 */
	public function delete($table, $where)
	{
		return $this->execute("DELETE FROM " . $this->_adapter->quoteField($this->_prefix.$table) . " WHERE $where");
	}

	/**
	 * Expand an association array to SQL 'SET' clause.
	 *
	 * @param array $data Array like array('field_1' => 'value_1, 'field_2' => 'value_2')
	 * @return The 'SET' cluase. For example: `field_1`='value_1', `field_2`=>'value_2'
	 */
	private function _implodeFields(array $data)
	{
		$fields = array();
		foreach($data as $key => $value)
		{
			$value = $this->_adapter->escapeValue($value);
			$fields[] = $this->_adapter->quoteField($key) . '=' . $this->_adapter->quoteValue($value);
		}

		return implode(',', $fields);
	}

	private function _beginTimer()
	{
		$this->_timer[0] = microtime(true);
	}

	private function _endTimer()
	{
		$this->_timer[1] = microtime(true);
		$time = $this->_timer[1] - $this->_timer[0];

		$this->_timer = array(0, 0);

		return $time;
	}
}
