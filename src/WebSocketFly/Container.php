<?php
/**
 * User: scil
 * Date: 2018/2/7
 * Time: 21:51
 */

namespace WebSocketFly;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Container
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected static $instance;

    public static function setInstance($container = null)
    {
        return static::$instance = $container ?: new ContainerBuilder()  ;
    }
    public static function getInstance()
    {
        return static::$instance;
    }
}