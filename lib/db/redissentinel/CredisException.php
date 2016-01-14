<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2016/1/13
 * Time: 10:09
 */



/**
 * Credis-specific errors, wraps native Redis errors
 */

namespace lib\db\redissentinel;

class CredisException extends \Exception
{

    const CODE_TIMED_OUT = 1;
    const CODE_DISCONNECTED = 2;

    public function __construct($message, $code = 0, $exception = NULL)
    {
        if ($exception && get_class($exception) == 'RedisException' && $message == 'read error on connection') {
            $code = CredisException::CODE_DISCONNECTED;
        }
        parent::__construct($message, $code, $exception);
    }
}
