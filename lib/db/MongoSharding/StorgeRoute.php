<?php
namespace lib\db\MongoSharding;

class StorgeRoute {
	
	/**
	 * 服务器配置
	 *
	 * @var unknown
	 */
	private $_serverConfig = array ();
	
	/**
	 * 构造
	 */
	public function __construct($config) {
		if (! is_array ( $config )) {
			throw new \Exception ( 'server config need to be an array' );
		}
		$this->_serverConfig = $config;
	}
	
	/**
	 * 获得服务器hash
	 */
	public function getShardingDsn($key) {
		$shardingHashInstance = new IMCP_DATA_SHARDING ();
		$shardingHashInstance->addNodes ( $this->_serverConfig );
		$nodeName = $shardingHashInstance->lookupNode ( $key );
		$serverNode = $this->_serverConfig [$nodeName];
		return $serverNode ['dsn'];
	}
	
	/**
	 * 获得分表的表id
	 * 仅文章分表
	 */
	public function getTableId($id, $tableBaseName) {
		$id = intval ( $id );
		if(!$id || !$tableBaseName){
			throw new \Exception ( 'hash table need int id and table base name' );
		}
		$tableid = IMCP_TABLE_HASH::getTableId ( $id );
		return $tableBaseName.'_'.$tableid;
	}
}