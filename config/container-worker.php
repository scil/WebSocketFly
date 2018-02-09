<?php
use Symfony\Component\DependencyInjection\Reference;

$container
    ->register('ipblock.source', \WebSocketFly\Middlewares\IpBlockSource\PhpFileSource::class)
    ->addArgument(__DIR__.'/ipblock-example.php')
;

$container
    ->register('ipblock', \WebSocketFly\Middlewares\IpBlockMiddleware::class)
    ->addArgument(new Reference('flyserver'))
    ->addArgument(new Reference('ipblock.source'))
;

$container
    ->register('id')
    ->setSynthetic(true)
;

