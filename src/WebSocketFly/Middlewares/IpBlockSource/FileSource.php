<?php
/**
 * User: scil
 * Date: 2018/2/8
 * Time: 0:40
 */

namespace WebSocketFly\Middlewares\IpBlockSource;


class FileSource implements IpBlockSource
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
        $path_parts = pathinfo($this->file);
        $time= date('Ymd-h-i-s');
        rename($this->file, "${path_parts['dirname']}/${path_parts['filename']}-$time.${path_parts['extension']}");
        file_put_contents($this->file, '<?'
            . 'php return '
            . var_export($list, true)
            . '   ; ?>'
        );
    }
}