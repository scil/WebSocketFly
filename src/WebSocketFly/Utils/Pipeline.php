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

    protected $method = 'handle';
    protected $pipes = [];
    /**
     * @var \Closure
     */
    protected $pipeline;
    protected $passablesNo;

    public function __construct(int $no)
    {
        if ($no > 3) {
            throw new \Exception(__CLASS__ . ": only support 2 or 3 passables");
        }
        $this->passablesNo = $no;
    }

    public function run(array $passables)
    {

        if (count($passables) != $this->passablesNo) {
            throw new \Exception(__CLASS__ . ": thie pipeline has $this->passablesNo passables");
        }
        return call_user_func_array($this->pipeline, $passables);
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
        if ($this->passablesNo == 2) {
            return function ($passable1, $passable2) use ($destination) {
                return $destination($passable1, $passable2);
            };
        } elseif ($this->passablesNo == 3) {
            return function ($passable1, $passable2, $passable3) use ($destination) {
                return $destination($passable1, $passable2, $passable3);
            };
        }
    }

    protected function carry()
    {
        return function ($stack, $pipe) {
            if ($this->passablesNo == 2) {

                return function ($passable1, $passable2) use ($stack, $pipe) {
                    if (is_callable($pipe)) {
                        return $pipe($passable1, $passable2);
                    } else {
                        $parameters = [$passable1, $passable2, $stack];
                    }
                    return method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);
                };

            } elseif ($this->passablesNo == 3) {

                return function ($passable1, $passable2, $passable3) use ($stack, $pipe) {
                    if (is_callable($pipe)) {
                        return $pipe($passable1, $passable2, $passable3, $stack);
                    } else {
                        $parameters = [$passable1, $passable2, $passable3, $stack];
                    }
                    return method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);
                };
            }
        };
    }
}