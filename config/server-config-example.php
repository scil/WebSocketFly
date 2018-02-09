<?php

return [
    /**
     * options:
     *      \LaravelFly\Server\WebSocketServer::class
     *      \LaravelFly\Server\HttpServer::class
     */
    'server' => \WebSocketFly\Server::class,

    /**
     * this is not for \LaravelFly\Server\WebSocketServer which always uses '0.0.0.0'
     * extend it and overwrite its __construct() if you need different listen_ip,
     */
    // 'listen_ip' => '127.0.0.1',// listen only to localhost
    'listen_ip' => '0.0.0.0',// listen to any address

    'listen_port' => 9509,

    'services_with_server'=>
"
services:
    flyserver:
        synthetic: true
",

    'services_with_worker'=>
"
services:
    ipblock.source:
        class:     \WebSocketFly\Middlewares\IpBlockSource\PhpFileSource
        arguments: ['".__DIR__."/ipblock-example.php']
    ipblock:
        class:      \WebSocketFly\Middlewares\IpBlockMiddleware
        arguments: ['@flyserver','@ipblock.source']
",

    'handlers'=>[
        'ipblock',
    ],
    // like pm.start_servers in php-fpm, but there's no option like pm.max_children
    'worker_num' => 1,

    // max number of coroutines handled by a worker in the same time
    'max_coro_num' => 3000,

    // set it to false when debug, otherwise true
    'daemonize' => false,

    // like pm.max_requests in php-fpm
    'max_request' => 1000,

    //'group' => 'www-data',

    //'log_file' => '/data/log/swoole.log',

    /** Set the output buffer size in the memory.
     * The default value is 2M. The data to send can't be larger than buffer_output_size every times.
     */
    //'buffer_output_size' => 32 * 1024 *1024, // byte in unit


    /**
     * make sure the pid_file can be writeable/readable by vendor/bin/laravelfly-server
     * otherwise use `sudo vendor/bin/laravelfly-server` or `chmod -R 777 <pid_dir>`
     *
     * default is under <project_root>/bootstrap/
     */
    //'pid_file' => '/run/laravelfly/pid',



];
