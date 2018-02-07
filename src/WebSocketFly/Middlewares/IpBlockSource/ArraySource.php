<?php
/**
 * User: scil
 * Date: 2018/2/8
 * Time: 0:40
 */

namespace WebSocketFly\Middlewares\IpBlockSource;


class File implements IpBlockSource
{
    protected $file;

    function __construct(string $f)
    {
        $this->file = $f;
    }

    function read()
    {
        return (require $this->file);
    }

    function save(array $list)
    {

        file_put_contents($this->file, '<?'
            . 'php return '
            . var_export($list, true)
            . '   ; ?>'
        );
    }
}