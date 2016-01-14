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

use lib\db\Predis\ClientException;

/**
 * Factory class for creating profile instances from strings.
 *
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
final class Factory
{
    private static $profiles = array(
        '2.0' => '\lib\db\Predis\Profile\RedisVersion200',
        '2.2' => '\lib\db\Predis\Profile\RedisVersion220',
        '2.4' => '\lib\db\Predis\Profile\RedisVersion240',
        '2.6' => '\lib\db\Predis\Profile\RedisVersion260',
        '2.8' => '\lib\db\Predis\Profile\RedisVersion280',
        '3.0' => '\lib\db\Predis\Profile\RedisVersion300',
        'dev' => '\lib\db\Predis\Profile\RedisUnstable',
        'default' => '\lib\db\Predis\Profile\RedisVersion300',
    );

    /**
     *
     */
    private function __construct()
    {
        // NOOP
    }

    /**
     * Returns the default server profile.
     *
     * @return ProfileInterface
     */
    public static function getDefault()
    {
        return self::get('default');
    }

    /**
     * Returns the development server profile.
     *
     * @return ProfileInterface
     */
    public static function getDevelopment()
    {
        return self::get('dev');
    }

    /**
     * Registers a new server profile.
     *
     * @param string $alias Profile version or alias.
     * @param string $class FQN of a class implementing Predis\Profile\ProfileInterface.
     *
     * @throws \InvalidArgumentException
     */
    public static function define($alias, $class)
    {
        $reflection = new \ReflectionClass($class);

        if (!$reflection->isSubclassOf('Predis\Profile\ProfileInterface')) {
            throw new \InvalidArgumentException("The class '$class' is not a valid profile class.");
        }

        self::$profiles[$alias] = $class;
    }

    /**
     * Returns the specified server profile.
     *
     * @param string $version Profile version or alias.
     *
     * @throws ClientException
     *
     * @return ProfileInterface
     */
    public static function get($version)
    {
        if (!isset(self::$profiles[$version])) {
            throw new ClientException("Unknown server profile: '$version'.");
        }

        $profile = self::$profiles[$version];

        return new $profile();
    }
}
