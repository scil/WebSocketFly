<?php
/**
 * User: scil
 * Date: 2018/2/6
 * Time: 18:33
 */

namespace WebSocketFly\Middlewares;


class IpBlockMiddleware
{
    /**
     * @var \WebSocketFly\Server
     */
    protected $server;

    function __construct(\WebSocketFly\Server $server)
    {
        $this->server = $server;
    }

    function onHandShake(\swoole_http_request $request,\swoole_http_response $response, $next)
    {
        $request->newReq=3;
        $response->end();
        return false;
        echo "[ip]: handle success with fd{$request->fd}\n";

        return $next($request, $response);

    }
}