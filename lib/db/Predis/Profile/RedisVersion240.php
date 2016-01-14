<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace lib\db\Predis\Profile;

/**
 * Server profile for Redis 2.4.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class RedisVersion240 extends RedisProfile
{
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.4';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedCommands()
    {
        return array(
            /* ---------------- Redis 1.2 ---------------- */

            /* commands operating on the key space */
            'EXISTS' => '\lib\db\Predis\Command\KeyExists',
            'DEL' => '\lib\db\Predis\Command\KeyDelete',
            'TYPE' => '\lib\db\Predis\Command\KeyType',
            'KEYS' => '\lib\db\Predis\Command\KeyKeys',
            'RANDOMKEY' => '\lib\db\Predis\Command\KeyRandom',
            'RENAME' => '\lib\db\Predis\Command\KeyRename',
            'RENAMENX' => '\lib\db\Predis\Command\KeyRenamePreserve',
            'EXPIRE' => '\lib\db\Predis\Command\KeyExpire',
            'EXPIREAT' => '\lib\db\Predis\Command\KeyExpireAt',
            'TTL' => '\lib\db\Predis\Command\KeyTimeToLive',
            'MOVE' => '\lib\db\Predis\Command\KeyMove',
            'SORT' => '\lib\db\Predis\Command\KeySort',

            /* commands operating on string values */
            'SET' => '\lib\db\Predis\Command\StringSet',
            'SETNX' => '\lib\db\Predis\Command\StringSetPreserve',
            'MSET' => '\lib\db\Predis\Command\StringSetMultiple',
            'MSETNX' => '\lib\db\Predis\Command\StringSetMultiplePreserve',
            'GET' => '\lib\db\Predis\Command\StringGet',
            'MGET' => '\lib\db\Predis\Command\StringGetMultiple',
            'GETSET' => '\lib\db\Predis\Command\StringGetSet',
            'INCR' => '\lib\db\Predis\Command\StringIncrement',
            'INCRBY' => '\lib\db\Predis\Command\StringIncrementBy',
            'DECR' => '\lib\db\Predis\Command\StringDecrement',
            'DECRBY' => '\lib\db\Predis\Command\StringDecrementBy',

            /* commands operating on lists */
            'RPUSH' => '\lib\db\Predis\Command\ListPushTail',
            'LPUSH' => '\lib\db\Predis\Command\ListPushHead',
            'LLEN' => '\lib\db\Predis\Command\ListLength',
            'LRANGE' => '\lib\db\Predis\Command\ListRange',
            'LTRIM' => '\lib\db\Predis\Command\ListTrim',
            'LINDEX' => '\lib\db\Predis\Command\ListIndex',
            'LSET' => '\lib\db\Predis\Command\ListSet',
            'LREM' => '\lib\db\Predis\Command\ListRemove',
            'LPOP' => '\lib\db\Predis\Command\ListPopFirst',
            'RPOP' => '\lib\db\Predis\Command\ListPopLast',
            'RPOPLPUSH' => '\lib\db\Predis\Command\ListPopLastPushHead',

            /* commands operating on sets */
            'SADD' => '\lib\db\Predis\Command\SetAdd',
            'SREM' => '\lib\db\Predis\Command\SetRemove',
            'SPOP' => '\lib\db\Predis\Command\SetPop',
            'SMOVE' => '\lib\db\Predis\Command\SetMove',
            'SCARD' => '\lib\db\Predis\Command\SetCardinality',
            'SISMEMBER' => '\lib\db\Predis\Command\SetIsMember',
            'SINTER' => '\lib\db\Predis\Command\SetIntersection',
            'SINTERSTORE' => '\lib\db\Predis\Command\SetIntersectionStore',
            'SUNION' => '\lib\db\Predis\Command\SetUnion',
            'SUNIONSTORE' => '\lib\db\Predis\Command\SetUnionStore',
            'SDIFF' => '\lib\db\Predis\Command\SetDifference',
            'SDIFFSTORE' => '\lib\db\Predis\Command\SetDifferenceStore',
            'SMEMBERS' => '\lib\db\Predis\Command\SetMembers',
            'SRANDMEMBER' => '\lib\db\Predis\Command\SetRandomMember',

            /* commands operating on sorted sets */
            'ZADD' => '\lib\db\Predis\Command\ZSetAdd',
            'ZINCRBY' => '\lib\db\Predis\Command\ZSetIncrementBy',
            'ZREM' => '\lib\db\Predis\Command\ZSetRemove',
            'ZRANGE' => '\lib\db\Predis\Command\ZSetRange',
            'ZREVRANGE' => '\lib\db\Predis\Command\ZSetReverseRange',
            'ZRANGEBYSCORE' => '\lib\db\Predis\Command\ZSetRangeByScore',
            'ZCARD' => '\lib\db\Predis\Command\ZSetCardinality',
            'ZSCORE' => '\lib\db\Predis\Command\ZSetScore',
            'ZREMRANGEBYSCORE' => '\lib\db\Predis\Command\ZSetRemoveRangeByScore',

            /* connection related commands */
            'PING' => '\lib\db\Predis\Command\ConnectionPing',
            'AUTH' => '\lib\db\Predis\Command\ConnectionAuth',
            'SELECT' => '\lib\db\Predis\Command\ConnectionSelect',
            'ECHO' => '\lib\db\Predis\Command\ConnectionEcho',
            'QUIT' => '\lib\db\Predis\Command\ConnectionQuit',

            /* remote server control commands */
            'INFO' => '\lib\db\Predis\Command\ServerInfo',
            'SLAVEOF' => '\lib\db\Predis\Command\ServerSlaveOf',
            'MONITOR' => '\lib\db\Predis\Command\ServerMonitor',
            'DBSIZE' => '\lib\db\Predis\Command\ServerDatabaseSize',
            'FLUSHDB' => '\lib\db\Predis\Command\ServerFlushDatabase',
            'FLUSHALL' => '\lib\db\Predis\Command\ServerFlushAll',
            'SAVE' => '\lib\db\Predis\Command\ServerSave',
            'BGSAVE' => '\lib\db\Predis\Command\ServerBackgroundSave',
            'LASTSAVE' => '\lib\db\Predis\Command\ServerLastSave',
            'SHUTDOWN' => '\lib\db\Predis\Command\ServerShutdown',
            'BGREWRITEAOF' => '\lib\db\Predis\Command\ServerBackgroundRewriteAOF',

            /* ---------------- Redis 2.0 ---------------- */

            /* commands operating on string values */
            'SETEX' => '\lib\db\Predis\Command\StringSetExpire',
            'APPEND' => '\lib\db\Predis\Command\StringAppend',
            'SUBSTR' => '\lib\db\Predis\Command\StringSubstr',

            /* commands operating on lists */
            'BLPOP' => '\lib\db\Predis\Command\ListPopFirstBlocking',
            'BRPOP' => '\lib\db\Predis\Command\ListPopLastBlocking',

            /* commands operating on sorted sets */
            'ZUNIONSTORE' => '\lib\db\Predis\Command\ZSetUnionStore',
            'ZINTERSTORE' => '\lib\db\Predis\Command\ZSetIntersectionStore',
            'ZCOUNT' => '\lib\db\Predis\Command\ZSetCount',
            'ZRANK' => '\lib\db\Predis\Command\ZSetRank',
            'ZREVRANK' => '\lib\db\Predis\Command\ZSetReverseRank',
            'ZREMRANGEBYRANK' => '\lib\db\Predis\Command\ZSetRemoveRangeByRank',

            /* commands operating on hashes */
            'HSET' => '\lib\db\Predis\Command\HashSet',
            'HSETNX' => '\lib\db\Predis\Command\HashSetPreserve',
            'HMSET' => '\lib\db\Predis\Command\HashSetMultiple',
            'HINCRBY' => '\lib\db\Predis\Command\HashIncrementBy',
            'HGET' => '\lib\db\Predis\Command\HashGet',
            'HMGET' => '\lib\db\Predis\Command\HashGetMultiple',
            'HDEL' => '\lib\db\Predis\Command\HashDelete',
            'HEXISTS' => '\lib\db\Predis\Command\HashExists',
            'HLEN' => '\lib\db\Predis\Command\HashLength',
            'HKEYS' => '\lib\db\Predis\Command\HashKeys',
            'HVALS' => '\lib\db\Predis\Command\HashValues',
            'HGETALL' => '\lib\db\Predis\Command\HashGetAll',

            /* transactions */
            'MULTI' => '\lib\db\Predis\Command\TransactionMulti',
            'EXEC' => '\lib\db\Predis\Command\TransactionExec',
            'DISCARD' => '\lib\db\Predis\Command\TransactionDiscard',

            /* publish - subscribe */
            'SUBSCRIBE' => '\lib\db\Predis\Command\PubSubSubscribe',
            'UNSUBSCRIBE' => '\lib\db\Predis\Command\PubSubUnsubscribe',
            'PSUBSCRIBE' => '\lib\db\Predis\Command\PubSubSubscribeByPattern',
            'PUNSUBSCRIBE' => '\lib\db\Predis\Command\PubSubUnsubscribeByPattern',
            'PUBLISH' => '\lib\db\Predis\Command\PubSubPublish',

            /* remote server control commands */
            'CONFIG' => '\lib\db\Predis\Command\ServerConfig',

            /* ---------------- Redis 2.2 ---------------- */

            /* commands operating on the key space */
            'PERSIST' => '\lib\db\Predis\Command\KeyPersist',

            /* commands operating on string values */
            'STRLEN' => '\lib\db\Predis\Command\StringStrlen',
            'SETRANGE' => '\lib\db\Predis\Command\StringSetRange',
            'GETRANGE' => '\lib\db\Predis\Command\StringGetRange',
            'SETBIT' => '\lib\db\Predis\Command\StringSetBit',
            'GETBIT' => '\lib\db\Predis\Command\StringGetBit',

            /* commands operating on lists */
            'RPUSHX' => '\lib\db\Predis\Command\ListPushTailX',
            'LPUSHX' => '\lib\db\Predis\Command\ListPushHeadX',
            'LINSERT' => '\lib\db\Predis\Command\ListInsert',
            'BRPOPLPUSH' => '\lib\db\Predis\Command\ListPopLastPushHeadBlocking',

            /* commands operating on sorted sets */
            'ZREVRANGEBYSCORE' => '\lib\db\Predis\Command\ZSetReverseRangeByScore',

            /* transactions */
            'WATCH' => '\lib\db\Predis\Command\TransactionWatch',
            'UNWATCH' => '\lib\db\Predis\Command\TransactionUnwatch',

            /* remote server control commands */
            'OBJECT' => '\lib\db\Predis\Command\ServerObject',
            'SLOWLOG' => '\lib\db\Predis\Command\ServerSlowlog',

            /* ---------------- Redis 2.4 ---------------- */

            /* remote server control commands */
            'CLIENT' => '\lib\db\Predis\Command\ServerClient',
        );
    }
}
