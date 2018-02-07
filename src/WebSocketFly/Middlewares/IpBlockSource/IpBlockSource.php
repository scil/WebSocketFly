<?php
/**
 * User: scil
 * Date: 2018/2/8
 * Time: 0:39
 */

namespace WebSocketFly\Middlewares\IpBlockSource;


interface IpBlockSource
{
    function read();

    function save(array $list);
}