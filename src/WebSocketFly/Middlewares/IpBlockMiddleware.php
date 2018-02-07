<?php
/**
 * User: scil
 * Date: 2018/2/6
 * Time: 18:33
 */

namespace WebSocketFly\Middlewares;

use WebSocketFly\Middlewares\IpBlockSource\IpBlockSource;

class IpBlockMiddleware
{
    protected $source;

    /**
     * @var array
     */
    protected $blacklist = [];

    function __construct(\WebSocketFly\Server $server, IpBlockSource $source)
    {
        $this->source = $source;
        $this->blacklist = $source->read();
        //todo save reg event listen

        $server->addShutdownCallback(function (){
            $this->blockAddress('127.1.0.0');
            $this->source->save($this->blacklist);
        });

    }

    function onHandShake(\swoole_http_request $request, \swoole_http_response $response, $next)
    {
        if ($this->isBlocked($request->server['remote_addr'])) {
            $response->status(403);
            $response->end();
            return false;
        }
        return $next($request, $response);

    }


    public function blockAddress($ip): IpBlockMiddleware
    {
        $this->blacklist[$ip] = true;
        return $this;
    }

    public function unblockAddress($ip): IpBlockMiddleware
    {
        if (isset($this->blacklist[$ip])) {
            unset($this->blacklist[$ip]);
        }
        return $this;
    }

    public function isBlocked($address): bool
    {
        return (isset($this->blacklist[$address]));
    }

    public function getBlockedAddresses(): array
    {
        return array_keys($this->blacklist);
    }
}