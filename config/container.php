<?php
use Symfony\Component\DependencyInjection\Reference;

$container
    ->register('server', \WebSocketFly\Server::class);

$container
    ->register('ipblock.source', \WebSocketFly\Middlewares\IpBlockSource\FileSource::class)
    ->addArgument(__DIR__.'/ipblock-example.php');

$container
    ->register('ipblock', \WebSocketFly\Middlewares\IpBlockMiddleware::class)
    ->addArgument(new Reference('server'))
    ->addArgument(new Reference('ipblock.source'));
