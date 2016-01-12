<?php

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2015/11/30
 * Time: 4:47
 */
class MemcacheWrapper
{
    /**
     * @var array
     */
    private $_config;
    /**
     * @var string
     */
    private $_keyPrefix = '';
    /**
     * @var boolean
     */
    protected $_exceptionDisabled = false;
    /**
     * @var boolean
     */
    private static $_baseServerInfo = array(
        'persistent' => false,
        'weight' => 100,
        'timeout' => 1
    );
    /**
     * @var Memcache
     */
    private $_memcache;

    /**
     * @var Array
     */
    private $_errormap = array(
        0 => 'MEMCACHED_SUCCESS',
        1 => 'MEMCACHED_FAILURE',
        2 => 'MEMCACHED_HOST_LOOKUP_FAILURE', // getaddrinfo() and getnameinfo() only
        3 => 'MEMCACHED_CONNECTION_FAILURE',
        4 => 'MEMCACHED_CONNECTION_BIND_FAILURE', // DEPRECATED see MEMCACHED_HOST_LOOKUP_FAILURE
        5 => 'MEMCACHED_WRITE_FAILURE',
        6 => 'MEMCACHED_READ_FAILURE',
        7 => 'MEMCACHED_UNKNOWN_READ_FAILURE',
        8 => 'MEMCACHED_PROTOCOL_ERROR',
        9 => 'MEMCACHED_CLIENT_ERROR',
        10 => 'MEMCACHED_SERVER_ERROR', // Server returns "SERVER_ERROR"
        11 => 'MEMCACHED_CONNECTION_SOCKET_CREATE_FAILURE', // Server returns "ERROR"
        12 => 'MEMCACHED_DATA_EXISTS',
        13 => 'MEMCACHED_DATA_DOES_NOT_EXIST',
        14 => 'MEMCACHED_NOTSTORED',
        15 => 'MEMCACHED_STORED',
        16 => 'MEMCACHED_NOTFOUND',
        17 => 'MEMCACHED_MEMORY_ALLOCATION_FAILURE',
        18 => 'MEMCACHED_PARTIAL_READ',
        19 => 'MEMCACHED_SOME_ERRORS',
        20 => 'MEMCACHED_NO_SERVERS',
        21 => 'MEMCACHED_END',
        22 => 'MEMCACHED_DELETED',
        23 => 'MEMCACHED_VALUE',
        24 => 'MEMCACHED_STAT',
        25 => 'MEMCACHED_ITEM',
        26 => 'MEMCACHED_ERRNO',
        27 => 'MEMCACHED_FAIL_UNIX_SOCKET', // DEPRECATED
        28 => 'MEMCACHED_NOT_SUPPORTED',
        29 => 'MEMCACHED_NO_KEY_PROVIDED', /* Deprecated. Use MEMCACHED_BAD_KEY_PROVIDED! */
        30 => 'MEMCACHED_FETCH_NOTFINISHED',
        31 => 'MEMCACHED_TIMEOUT',
        32 => 'MEMCACHED_BUFFERED',
        33 => 'MEMCACHED_BAD_KEY_PROVIDED',
        34 => 'MEMCACHED_INVALID_HOST_PROTOCOL',
        35 => 'MEMCACHED_SERVER_MARKED_DEAD',
        36 => 'MEMCACHED_UNKNOWN_STAT_KEY',
        37 => 'MEMCACHED_E2BIG',
        38 => 'MEMCACHED_INVALID_ARGUMENTS',
        39 => 'MEMCACHED_KEY_TOO_BIG',
        40 => 'MEMCACHED_AUTH_PROBLEM',
        41 => 'MEMCACHED_AUTH_FAILURE',
        42 => 'MEMCACHED_AUTH_CONTINUE',
        43 => 'MEMCACHED_PARSE_ERROR',
        44 => 'MEMCACHED_PARSE_USER_ERROR',
        45 => 'MEMCACHED_DEPRECATED',
        46 => 'MEMCACHED_IN_PROGRESS',
        47 => 'MEMCACHED_SERVER_TEMPORARILY_DISABLED',
        48 => 'MEMCACHED_SERVER_MEMORY_ALLOCATION_FAILURE',
        49 => 'MEMCACHED_MAXIMUM_RETURN', /* Always add new error code before */
    );


    /**
     * __construct would throw RuntimeException if config is illigal.
     * @param array $config
     */
    public function __construct($config)
    {
        if (!isset ($config ['servers']) || !is_array($config ['servers'])) {
            /**
             * RuntimeException: Exception thrown if an error which can only be found on runtime occurs.
             */
            throw new RuntimeException ('illigal config, servers is required');
        }
        if (isset ($config ['keyPrefix'])) {
            $this->_keyPrefix = $config ['keyPrefix'];
        }
        if (isset ($config ['exceptionDisabled'])) {
            $this->_exceptionDisabled = $config ['exceptionDisabled'];
        }
        $this->_config = $config;
    }

    /**
     * @see PI_Util_Storage_Interface::put()
     * @return boolean
     */
    public function set($key, $value, $timeout = 0)
    {
        $memcache = $this->_getMemcache();
        $result = $memcache->set($this->_key($key), $value, $timeout);
        if ($result === false) {
            return $this->_triggerException(array('key' => $key, 'msg' => 'fail to put data to memcache'));
        }
        return true;
    }

    /**
     * @see PI_Util_Storage_Interface::delete()
     * @return boolean
     */
    public function delete($key)
    {
        $result = @$this->_getMemcache()->delete($this->_key($key));
        if ($result === false) {
            return $this->_triggerException(array('key' => $key, 'msg' => 'fail to delete data from memcache'));
        }
        return true;
    }

    /**
     * @see PI_Util_Storage_Interface::get()
     * @return string;
     */
    public function get($key)
    {
        $result = @$this->_getMemcache()->get($this->_key($key));
        if ($result === false) {
            return $this->_triggerException(array('key' => $key, 'msg' => 'fail to get(' . $key . ') data from memcache'));
        }
        return $result;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function _key($key)
    {
        if (is_array($key)) {
            foreach ($key as &$v) {
                $v = $this->_keyPrefix . $v;
            }
            return $key;
        }
        return $this->_keyPrefix . $key;
    }

    /**
     * set consistent-hash
     * 每次set会自动摘除问题服务器；每次get，前两次失败后，会成功查出问题服务器
     * @param void
     * @return void
     */
    protected function _setOption($switch)
    {
        if ($this->_memcache && $switch) {
            $this->_memcache->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
            $this->_memcache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            $this->_memcache->setOption(Memcached::OPT_REMOVE_FAILED_SERVERS, true);
        }
    }

    /**
     * @return Memcache
     */
    protected function _getMemcache()
    {
        if (!$this->_memcache) {
            if (!class_exists('Memcached')) {
                throw new RuntimeException ('memcached class is required');
            }
            //如果使用长连接， 那么一致性哈希将不能生效
            $this->_memcache = new Memcached (); // persistence
            if (is_array($this->_config['servers']) && count($this->_config['servers']) > 1) {
                $this->_setOption(true);
            }
            foreach ($this->_config ['servers'] as $serverInfo) {
                $this->_addServer($serverInfo);
            }
        }
        return $this->_memcache;
    }

    /**
     * @param array $serverInfo
     */
    protected function _addServer($serverInfo)
    {
        if (!isset ($serverInfo ['host']) || !isset ($serverInfo ['port'])) {
            throw new RuntimeException ('illigal server info, host and port is required');
        }
        $serverInfo = array_merge(self::$_baseServerInfo, $serverInfo);
        $servers = $this->_memcache->getServerList();
        if (is_array($servers)) {
            foreach ($servers as $server)
                if ($server ['host'] == $serverInfo['host'] && $server ['port'] == $serverInfo['port'])
                    return true;
        }
        $this->_memcache->addServer($serverInfo ['host'], $serverInfo ['port']);
    }

    /**
     * always returns false if $_exceptionDisabled equals true, else throws exception
     * @param string $message
     * @return boolean
     * @throws PI_Util_Storage_Exception
     */
    protected function _triggerException($data)
    {
        if ($this->_exceptionDisabled) {
            return false;
        }
        $serverStatus = '';
        $err_code = @$this->_getMemcache()->getResultCode();
        $err_code_str = isset($this->_errormap[$err_code]) ? $this->_errormap[$err_code] : strval($err_code);
        $serverinfo = @$this->_getMemcache()->getServerByKey($data['key']);
        $serverStatus .= $serverinfo['host'] . ':' . $serverinfo['port'] . ' err_code[' . $err_code_str . ']';
        throw new Exception($data['msg'] . ' with err_code [' . $serverStatus . ']');
    }
}
