<?php
namespace lib\db\MongoSharding;

class IMCP_DATA_SHARDING {
	
	/**
	 * 节点记数器
	 */
	private $_nodeCount = 0;
	
	/**
	 * 节点的环
	 */
	private $_positionToNode = array ();
	
	/**
	 * 位置节点对应关系
	 */
	private $_nodeToPositions = array ();
	
	/**
	 * hash空间大小
	 *
	 * @var unknown
	 */
	const HASH_SPACE = 1000;
	
	/**
	 */
	public function __construct() {
	}
	
	/**
	 * 添加节点,暂时不考虑虚拟节点
	 */
	private function _addNode($name, $node) {
		$nodename = $name;
		$position = intval ( ($node ['positionIndex'] / $node ['positionBase']) * self::HASH_SPACE );
		if (! $nodename || !isset($node ['positionIndex']) || !isset($node ['positionBase'])|| isset ( $this->_nodeToPositions [$nodename] )) {
			throw new \Exception ( "Node '$node' already exists." );
		}
		$this->_positionToNode [$position] = $nodename; // lookup
		$this->_nodeToPositions [$nodename] = $position;
		
		$this->_nodeCount ++;
		
		return $this;
	}
	
	/**
	 * 增加一个节点
	 */
	public function addNodes($nodes) {
		foreach ( $nodes as $name => $node ) {
			$this->_addNode ( $name, $node );
		}
		// 对环进行排序
		$this->_sortPositionNodes ();
		return $this;
	}
	
	/**
	 * Remove a node.
	 *
	 * @param string $node
	 *        	@chainable
	 *        	
	 */
	public function removeNode($node) {
		if (! isset ( $this->_nodeToPositions [$node] )) {
			throw new \Exception ( "Node '$node' does not exist." );
		}
		
		foreach ( $this->_nodeToPositions [$node] as $position ) {
			unset ( $this->_positionToNode [$position] );
		}
		
		unset ( $this->_nodeToPositions [$node] );
		
		$this->_nodeCount --;
		
		return $this;
	}
	
	/**
	 * 获得全部节点
	 */
	public function getAllNodes() {
		var_dump ( $this->_nodeToPositions );
		var_dump ( $this->_positionToNode );
		return array_keys ( $this->_nodeToPositions );
	}
	
	/**
	 * 根据环寻找节点
	 */
	public function lookupNode($resource) {
		if (empty ( $this->_positionToNode )) {
			return null;
		}
		
		// 单个节点
		if ($this->_nodeCount == 1) {
			return array_unique ( array_values ( $this->_positionToNode ) );
		}
		
		// 取resource 的hash值
		$resourcePosition = $this->getHash ( $resource );
		
		$result = false;
		// 根据哈希值寻找合适的节点地址
		foreach ( $this->_positionToNode as $key => $value ) {
			if ($key > $resourcePosition) {
				$result = $value;
				break;
			}
		}
		if (! $result) {
			reset ( $this->_positionToNode );
			$result = current ( $this->_positionToNode );
		}
		
		return $result;
	}
	public function __toString() {
		return sprintf ( '%s{nodes:[%s]}', get_class ( $this ), implode ( ',', $this->getAllNodes () ) );
	}
	
	/**
	 * 对节点环进行排序
	 */
	private function _sortPositionNodes() {
		ksort ( $this->_positionToNode, SORT_REGULAR );
	}
	
	/**
	 * 使用crc进行hash操作
	 */
	public function getHash($key) {
		return crc32 ( $key ) % self::HASH_SPACE;
	}
}
