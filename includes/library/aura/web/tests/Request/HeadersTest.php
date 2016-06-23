<?php
namespace Aura\Web\Request;

class HeadersTest extends \PHPUnit_Framework_TestCase
{
    protected function newHeaders($server = array())
    {
        return new Headers($server);
    }

    public function testGet()
    {
        $server['HTTP_FOO'] = 'bar';
        $server['HTTP_QUX'] = 'quux';
        $server['CONTENT_TYPE'] = 'text/plain';
        $server['CONTENT_LENGTH'] = '42';
        $headers = $this->newHeaders($server);

        $actual = $headers->get('foo');
        $this->assertSame('bar', $actual);

        $actual = $headers->get('baz');
        $this->assertNull($actual);

        $actual = $headers->get('baz', 'dib');
        $this->assertSame('dib', $actual);

        $expect = array(
            'foo' => 'bar',
            'qux' => 'quux',
            'content-type' => 'text/plain',
            'content-length' => '42'
        );
        $actual = $headers->get();
        $this->assertSame($expect, $actual);
    }
}
