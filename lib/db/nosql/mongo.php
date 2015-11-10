<?php
class Storage_Mongo {
	/**
	 * db
	 */
	private $_db = '';
	/**
	 *
	 * @var boolean
	 */
	private $_baseOptions = array (
			'connect' => true,
			'connectTimeoutMS' => 1000 
	);
	
	/**
	 *
	 * @var obj
	 */
	private $_storgeRouteInstance = null;
	/**
	 *
	 * @var array MongoClient
	 */
	private $_mongoClient = array ();
	
	/**
	 * __construct would throw RuntimeException if config is illigal.
	 *
	 * @param array $config        	
	 */
	public function __construct() {
	}
	
	/**
	 * 初始化Db
	 */
	public function setDb($db) {
		$this->_db = $db;
	}
	
	/**
	 * 初始化config
	 */
	public function setServerConfig(storgeRoute $storgeRouteInstance) {
		$this->_storgeRouteInstance = $storgeRouteInstance;
	}
	
	/**
	 * get mongo instance
	 * try exception
	 */
	private function _getMongoConnection($dsn = "", $options = array(), $retry = 3) {
		try {
			return new MongoClient ( $dsn, $options );
		} catch ( Exception $e ) {
		}
		
		if ($retry > 0) {
			return $this->_getMongoConnection ( $dsn, $options, -- $retry );
		}
		throw new Exception ( "cant connect to mongo server" );
	}
	
	/**
	 * get mongo
	 */
	private function getInstance($dsn) {
		$key = md5 ( $dsn );
		if (! isset ( $this->_mongoClient [$key] ) || ! $this->_mongoClient [$key]) {
			if (! class_exists ( 'MongoClient' )) {
				throw new Exception ( 'class MongoClient is required' );
			}
			$this->_mongoClient [$key] = $this->_getMongoConnection ( $dsn, $this->_baseOptions );
		}
		
		return $this->_mongoClient [$key];
	}
	
	/**
	 * 分库分表
	 * 返回collection
	 */
	public function getCollection($key, $collection, $hashTable) {
		// get server sharding
		$dsn = $this->_storgeRouteInstance->getShardingDsn ( $key );
		;
		$db = $this->_db;
		if ($hashTable) {
			$collection = $this->_storgeRouteInstance->getTableId ( $key, $collection );
		}
		if (! $db || ! $collection) {
			throw new Exception ( 'Mongo config db && collection is required' );
		}
		$mongo = $this->getInstance ( $dsn );
		// 返回collection
		$mc = $mongo->$db->$collection;
		// $mc->setReadPreference(\MongoClient::RP_PRIMARY);
		// $mc->setReadPreference(\MongoClient::RP_PRIMARY_PREFERRED);
		// $mc->setReadPreference(\MongoClient::RP_SECONDARY);
		// $mc->setReadPreference ( \MongoClient::RP_SECONDARY_PREFERRED );
		$mc->setReadPreference ( \MongoClient::RP_NEAREST );
		
		return $mc;
	}
	
	/**
	 * get function
	 */
	public function get($key, $collectionName, $hashTable = false, $intId = false, $keyPrefix = '') {
		$key = $this->_key ( $key, $intId, $keyPrefix );
		// 获取collection
		try {
			// key分为数组和非数组
			if (is_array ( $key )) {
				$data = array ();
				foreach ( $key as $k ) {
					$collection = $this->getCollection ( $k, $collectionName, $hashTable );
					$result = $collection->findOne ( array (
							'_id' => $k 
					) );
					if ($result ['data']) {
						$data [$result ['_id']] = $result ['data'];
					}
				}
				return json_encode ( $data, JSON_UNESCAPED_UNICODE );
			} else {
				$collection = $this->getCollection ( $key, $collectionName, $hashTable );
				$result = $collection->findOne ( array (
						'_id' => $key 
				) );
				if (! is_array ( $result ) || ! count ( $result )) {
					throw new Exception ( 'get key failed' );
				}
				return $result ['data'];
			}
		} catch ( Exception $e ) {
			throw new Exception ( 'get key ' . $key . ' failed!' );
		}
	}
	
	/**
	 * set value
	 *
	 * @see PI_Util_Storage_Interface::put()
	 */
	public function put($key, $value, $collectionName, $hashTable = false, $intId = false, $keyPrefix = '') {
		if (! $key || ! $value) {
			return false;
		}
		$key = $this->_key ( $key, $intId, $keyPrefix );
		
		$saveData = array (
				'_id' => $key,
				'data' => $value 
		);
		
		try {
			$collection = $this->getCollection ( $key, $collectionName, $hashTable );
			$result = $collection->save ( $saveData );
		} catch ( Exception $e ) {
			$result = false;
		}
		if (is_array ( $result ) && intval ( $result ['ok'] ) == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * delete value
	 *
	 * @see PI_Util_Storage_Interface::delete()
	 */
	public function delete($key, $collectionName, $hashTable = false, $intId = false, $keyPrefix = '') {
		$key = $this->_key ( $key, $intId, $keyPrefix );
		try {
			$collection = $this->getCollection ( $key, $collectionName, $hashTable );
			$result = $collection->remove ( array (
					'_id' => $key 
			), array (
					"justOne" => true 
			) );
		} catch ( Exception $e ) {
			return false;
		}
		if (is_array ( $result ) && $result ['ok'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * increase value
	 */
	public function increase($key, $collectionName, $hashTable = false, $intId = false, $keyPrefix = '') {
		$key = $this->_key ( $key, $intId, $keyPrefix );
		
		try {
			$collection = $this->getCollection ( $key, $collectionName, $hashTable );
		} catch ( Exception $e ) {
			return false;
		}
		
		try {
			$return = $collection->findAndModify ( array (
					'_id' => $key 
			), array (
					'$inc' => array (
							'data' => 1 
					) 
			), array (), array (
					'new' => true,
					'upsert' => true 
			) );
		} catch ( Exception $e ) {
			return false;
		}
		if (is_array ( $return ) && $return ['ok'] == 1) {
			return $return ['data'];
		} else {
			return false;
		}
	}
	
	/**
	 *
	 * @param string $key        	
	 * @return string
	 */
	private function _key($key, $intId = false, $keyPrefix = '') {
		if (is_array ( $key )) {
			foreach ( $key as &$v ) {
				if ($intId) {
					$v = intval ( $v );
				} else {
					$v = $keyPrefix . $v;
				}
			}
			return $key;
		} else {
			if ($intId) {
				return intval ( $key );
			} else {
				return strval ( $keyPrefix . $key );
			}
		}
	}
}