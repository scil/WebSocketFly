<?php
/**
 * User: scil
 * Date: 2018/2/6
 * Time: 18:33
 */

namespace WebSocketFly\Middlewares;


class BaseMiddleware
{
    /**
     * @var \WebSocketFly\Server
     */
    protected $server;

    function __construct(\WebSocketFly\Server $server, \Closure $init = null)
    {
        $this->server = $server;
        if ($init)
            $init->call($this);
    }

    function onHandShake(\swoole_http_request $request, $next)
    {
        $request->newReq=3;
        echo "server: handle success with fd{$request->fd}\n";
        return $next($request);

    }
}