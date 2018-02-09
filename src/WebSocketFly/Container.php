<?php
/**
 * User: scil
 * Date: 2018/2/7
 * Time: 21:51
 */

namespace WebSocketFly;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Container extends  ContainerBuilder
{
    /**
     * @var Container
     */
    protected static $workerInstance;

    /**
     * @var Container
     */
    protected static $serverInstance;


    public static function setServerInstance()
    {

        return static::$serverInstance = new static();
    }
    /**
     * overwrite parent's private to protected
     */
    protected function __clone(){}

    public static function setWorkerInstance()
    {

        /**
         * another sulution is not `clone` but `new` and in method g(), first seek in $workerInstance and in $serverInstance.
         * but it's not good. because workerInstance would use services defined in serverInstance
         */
        return static::$workerInstance = clone static::$serverInstance;
    }

    public static function g($name)
    {
        if (static::$workerInstance->has($name)) {
            return static::$workerInstance->get($name);
        }
        throw new \Exception(__CLASS__ . ": no $name regstered.");
    }
}