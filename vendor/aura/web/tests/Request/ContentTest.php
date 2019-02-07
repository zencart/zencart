<?php
namespace Aura\Web\Request;

use Aura\Web\PhpStream;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', 'Aura\Web\PhpStream');
    }

    public function tearDown()
    {
        stream_wrapper_restore('php');
    }

    public function newContent($server = array(), $decoders = array())
    {
        return new Content($server, $decoders);
    }

    public function testGet()
    {
        $object = (object) array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        );
        $encode = json_encode($object);
        PhpStream::$content = $encode;

        $server = array(
            'CONTENT_TYPE' => 'application/json',
            'CONTENT_LENGTH' => '88',
            'HTTP_CONTENT_MD5' => 'foo'
        );

        $content = $this->newContent($server);

        $actual = $content->get();
        $this->assertEquals($object, $actual);

        $this->assertSame('application/json', $content->getType());
        $this->assertSame('88', $content->getLength());
        $this->assertSame('foo', $content->getMd5());
    }

    public function testIssue31()
    {
        $object = (object) array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        );
        $encode = json_encode($object);
        PhpStream::$content = $encode;

        $server = array(
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_CONTENT_LENGTH' => '88',
            'HTTP_CONTENT_MD5' => 'foo'
        );

        $content = $this->newContent($server);

        $actual = $content->get();
        $this->assertEquals($object, $actual);

        $this->assertSame('application/json', $content->getType());
        $this->assertSame('88', $content->getLength());
        $this->assertSame('foo', $content->getMd5());
    }

    public function testContentCharset()
    {
        $object = (object) array(
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        );
        $encode = json_encode($object);
        PhpStream::$content = $encode;

        $server = array(
            'CONTENT_TYPE' => 'application/json; charset=utf-8',
            'CONTENT_LENGTH' => '88',
            'HTTP_CONTENT_MD5' => 'foo'
        );

        $content = $this->newContent($server);

        $actual = $content->get();
        $this->assertEquals($object, $actual);

        $this->assertSame('application/json', $content->getType());
        $this->assertSame('utf-8', $content->getCharset());
        $this->assertSame('88', $content->getLength());
        $this->assertSame('foo', $content->getMd5());
    }

    public function testWithoutContentType()
    {
        PhpStream::$content = "foo=bar";
        $content = $this->newContent(array());
        $actual = $content->get();
        $this->assertEquals("foo=bar", $actual);
    }
}
