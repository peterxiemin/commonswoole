<?php
/**
 * Mysql Adapter class for DBLite
 *
 * $Id: Mysql.php 2348 2011-08-19 01:12:09Z wangjb $
 */
namespace lib\db\dblite\adapter;

class Mysql extends \lib\db\dblite\Adapter
{
	private $_pdo;
	
	private $_lastErrorInfo;
	
	private $_config;
	
	private $_connections;
	
	public function __construct($config, $id)
	{
		if (empty($id) || !isset($config['databases'][$id]))
		{
			throw new \lib\db\dblite\Exception('config file error,please specify database id');
		}
		$this->_config = $config;
		$this->_connections = $config['databases'][$id];
		$required = array('host', 'username', 'password', 'dbname');
		foreach($required as $key)
		{
			foreach ($this->_connections as $connection)
			{
				if(!isset($connection[$key]))
				{
					throw new \lib\db\dblite\Exception('MySQL adapter needs the following information: host, username, password and dbname, please check your configuration.');
				}
			}
		}
		if(!extension_loaded('pdo_mysql'))
		{
			throw new \lib\db\dblite\Exception('pdo_mysql extension is not loaded.');
		}
	}
	
	private function _generatePDO($connectionIndex)
	{
		$connection = $this->_connections[$connectionIndex];
		$port = isset($connection['port']) ? $connection['port'] : 3306;
		$charset = isset($connection['charset']) ? $connection['charset'] : null;
		$persistent = isset($connection['persistent']) ? $connection['persistent'] : false;
		$dsn = "mysql:host={$connection['host']};port={$port};dbname={$connection['dbname']}";
		try
		{
			$pdo = new \PDO($dsn, $connection['username'], $connection['password'], array(\PDO::ATTR_PERSISTENT => $persistent));
		}
		catch(PDOException $e)
		{
			$this->_lastErrorInfo = "[#Connection {$connectionIndex}#] - " . $e->getMessage();
			return false;
		}
		
		if(!empty($charset))
		{
			$pdo->exec("SET NAMES '$charset'");
		}
		$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); 

		$this->_pdo = $pdo;
		return $pdo;
	}
	
	public function createPDO()
	{
		$connectionCount = count($this->_connections);
		$randomIndex = rand(0, $connectionCount - 1);
		$pdo = $this->_generatePDO($randomIndex);
		$nextIndex = $randomIndex;
		$maxRetries = $this->_config['maxRetries'];
		$retries = 0;
		while(!$pdo)
		{
			$nextIndex = ($nextIndex + 1) % $connectionCount;
			$pdo = $this->_generatePDO($nextIndex);
			$retries++;
			if($retries >= $maxRetries)
			{
				break;
			}
		}
		if(!$pdo)
		{
			throw new \lib\db\dblite\Exception('Connection failed: ' . $this->_lastErrorInfo);
		}
		return $pdo;
	}
	
	public function getPrefix()
	{
		return "";
	}

	public function quoteField($field)
	{
		return "`$field`";
	}
	
	public function quoteValue($value)
	{
		return "'$value'";
	}

	public function explain($sql)
	{
		if(!isset($this->_pdo))
		{
			$this->createPDO();
		}

		$sql = trim($sql);
		if(strtoupper(substr($sql, 0, 6)) != 'SELECT')
		{
			throw new \lib\db\dblite\Exception('Mysql cannot explain non-SELECT SQL statements.');
		}

		$stmt = $this->_pdo->query("EXPLAIN $sql");
		$explainInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return $explainInfo;
	}
	
	public function escapeValue($value)
	{
		return addslashes($value);
	}
}
