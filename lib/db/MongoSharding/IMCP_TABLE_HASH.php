<?php
namespace lib\db\MongoSharding;

class IMCP_TABLE_HASH {
	
	/**
	 * const hash space
	 */
	const HASH_SPACE = 100;
	/**
	 * construct
	 */
	public function __construct() {
	}
	
	/**
	 * static get Hash Table id
	 */
	static function getTableId($id) {
		$id = intval ( $id );
		if (! $id) {
			throw new \Exception('invalid data type ,need to be int');
		}
		return $id % self::HASH_SPACE;
	}
}