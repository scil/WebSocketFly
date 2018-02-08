<?php
/**
 * User: scil
 * Date: 2018/2/2
 * Time: 23:49
 */

namespace WebSocketFly;

use WebSocketFly\Utils\Pipeline;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class Server
{

    /**
     * @var \swoole_websocket_server
     */
    var $server;

    /**
     * @var static
     */
    static $instance;

    /**
     * @var string
     */
    var $workerContainerFile;

    /**
     * @var array
     */
    var $handlers=[];

    /**
     * @var array
     */
    var $shutdownCallbacks=[];

    /**
     * @var \WebsocketFly\Utils\Pipeline
     */
    var $onHandshakePipeline;
    /**
     * @var \WebsocketFly\Utils\Pipeline
     */
    var $onOpenPipeline;
    /**
     * @var \WebsocketFly\Utils\Pipeline
     */
    var $onMessagePipeline;
    /**
     * @var \WebsocketFly\Utils\Pipeline
     */
    var $onClosePipeline;

    public function __construct(array $options)
    {

        $this->initServerContainer($options);

        $this->handlers = $options['handlers'];

        $this->initOptions($options);

        $this->server = $server = new \swoole_websocket_server($options['listen_ip'], $options['listen_port']);

        $server->set($options);

        $this->setListeners();

    }

    protected function initServerContainer($options)
    {
        $configFile = is_file($options['container_server'] ?? null) ?
            $options['container_server'] :
            __DIR__ . '/../../config/container-server.php';

        $this->workerContainerFile =  is_file($options['container_worker'] ?? null) ?
            $options['container_worker'] :
            __DIR__ . '/../../config/container-worker.php';

        $container = Container::setServerInstance();

        $container->set('flyserver', $this);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
        $loader->load($configFile);

    }

    protected function initOptions(&$options)
    {
        if (!isset($options['pid_file'])) {
            $options['pid_file'] = __DIR__ . '/../../bin/' . $options['listen_port'] . '.pid';
        } else {
            $options['pid_file'] = $options['pid_file'] . '-' . $options['listen_port'];
        }

    }

    protected function setListeners()
    {
        $this->server->on('handshake', array($this, 'onHandShake'));
        $this->server->on('open', array($this, 'onOpen'));
        $this->server->on('message', array($this, 'onMessage'));
        $this->server->on('close', array($this, 'onClose'));


        $this->server->on('workerstart', array($this, 'onWorkerStart'));
        $this->server->on('shutdown', array($this, 'onShutdown'));
    }

    public function onWorkerStart(\swoole_server $server, int $workerid)
    {
        /**
         * make sure reload middlewares and related files.
         * e.g. if not reset, the config/ipblock-example.php cant not be read again.
         */
        opcache_reset();

        $container = Container::setWorkerInstance();
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
        $loader->load($this->workerContainerFile);

        $container->set('id',$workerid);

        $this->setMiddlewares($this->handlers);

    }
    protected function setMiddlewares(array $middlewares)
    {
        $onHandshakeMiddlewares = [];
        $onOpenMiddlewares = [];
        $onMessageMiddlewares = [];
        $onCloseMiddlewares = [];

        $methods_list = ['onHandshake' => 2, 'onOpen' => 2, 'onMessage' => 2, 'onClose' => 2];

        foreach ($middlewares as $name) {
            $middleware = Container::g($name);
            foreach ($methods_list as $method => $no) {
                if (method_exists($middleware, $method)) {
                    ${$method . 'Middlewares'}[] = $middleware;
                }
            }
        }
        foreach ($methods_list as $method => $no) {
            $this->{$method . 'Pipeline'} = (new Pipeline($no))
                ->through(${$method . 'Middlewares'})
                ->via($method);
        }
    }

    function onShutdown()
    {

        foreach ($this->shutdownCallbacks as $c){
            $c();
        }
    }
    function addShutdownCallback($c){
        $this->shutdownCallbacks[]=$c;
    }
    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @return bool
     *
     * from: https://wiki.swoole.com/wiki/page/409.html
     */
    function onHandShake(\swoole_http_request $request, \swoole_http_response $response)
    {
        $key = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $key) || 16 !== strlen(base64_decode($key))) {
            $response->end();
            return false;
        }

        return $this->onHandshakePipeline
            ->then($this->acceptHandshake($key))
            ->run([$request, $response]);

    }

    function acceptHandshake($key)
    {
        return function ($request, $response) use ($key) {

            $key = base64_encode(sha1(
                $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
                true
            ));

            $response->header('Upgrade', 'websocket');
            $response->header('Connection', 'Upgrade');
            $response->header('Sec-WebSocket-Accept', $key);
            $response->header('Sec-WebSocket-Version', '13');

            // WebSocket connection to 'ws://127.0.0.1:9502/'
            // failed: Error during WebSocket handshake:
            // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
            if (isset($request->header['sec-websocket-protocol'])) {
                $response->header('Sec-WebSocket-Protocol', $request->header['sec-websocket-protocol']);
            }

            $response->status(101);
            $response->end();

            $this->server->defer(function () use ($request) {
                $this->onOpen($this->server, $request);
            });

            var_dump("officeal hand shake to return true");
            return true;

        };
    }

    function onOpen(\swoole_websocket_server $svr, \swoole_http_request $request)
    {
        $this->server->push($request->fd, 'ok oepn');
        $id= Container::g('id');
        echo "[on open] server: open success with fd{$request->fd}  worker:$id\n";
    }

    function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        try {
            $this->app->onMessage($conn->decor, $data);
        } catch (\Exception $e) {
            $this->handleError($e, $conn);
        }
    }

    function onClose(\swoole_websocket_server $server, $fd)
    {
        return $this->onClosePipeline
            ->then(function (){})
            ->run([$server, $fd]);
    }

    public function push($msg, $fd)
    {
        $this->server->push($fd, $msg);
    }

    public function pushToAll($msg)
    {
        foreach ($this->server->connections as $fd) {
            $this->server->push($fd, $msg);
        }
    }

    public static function getInstance($options)
    {
        if (!self::$instance) {
            try {
                self::$instance = new static($options);
            } catch (\Throwable $e) {
                die('[FAILED] ' . $e->getMessage() . PHP_EOL);
            }
        }
        return self::$instance;
    }


    public function start()
    {
        try {
            $this->server->start();
        } catch (\Throwable $e) {
            die('[FAILED] ' . $e->getMessage() . PHP_EOL);
        }
    }

}