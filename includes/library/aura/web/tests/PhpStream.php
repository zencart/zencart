<?php
namespace Aura\Web;

class PhpStream
{
    protected $pos = 0;

    static public $content = '';

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        return isset(self::$content);
    }

    public function stream_read($count)
    {
        $return = substr(self::$content, $this->pos, $count);
        $this->pos += strlen($return);
        return $return;
    }

    public function stream_eof()
    {
        return 0 == strlen(self::$content);
    }

    public function stream_stat()
    {
        return array();
    }
}
