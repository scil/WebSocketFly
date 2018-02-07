<?php
/**
 * User: scil
 * Date: 2018/2/7
 * Time: 1:10
 */

namespace WebSocketFly\Utils;


use Closure;
use RuntimeException;

class Pipeline
{

    protected $passable;
    protected $method = 'handle';
    protected $pipes = [];
    /**
     * @var \Closure
     */
    protected $pipeline;

    public function run($passable)
    {
        return call_user_func($this->pipeline,$passable);
    }
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }
    public function via($method)
    {
        $this->method = $method;

        return $this;
    }
    public function then(Closure $destination)
    {
        $this->pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
        );

        return $this;
    }

    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            return $destination($passable);
        };
    }

    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    // If the pipe is an instance of a Closure, we will just call it directly but
                    // otherwise we'll resolve the pipes out of the container and call it with
                    // the appropriate method and arguments, returning the results back out.
                    return $pipe($passable, $stack);
                } else {
                    // If the pipe is already an object we'll just make a callable and pass it to
                    // the pipe as-is. There is no need to do any extra parsing and formatting
                    // since the object we're given was already a fully instantiated object.
                    $parameters = [$passable, $stack];
                }

                return method_exists($pipe, $this->method)
                    ? $pipe->{$this->method}(...$parameters)
                    : $pipe(...$parameters);
            };
        };
    }
}