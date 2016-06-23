<?php
namespace Aura\Web\Response;

use DateTime;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected $cache;

    protected $headers;

    protected function setUp()
    {
        $this->headers = new Headers;
        $this->cache = new Cache($this->headers);
    }

    protected function assertHeaders(array $expect)
    {
        $actual = $this->headers->get();
        $this->assertSame($expect, $actual);
    }

    public function testReset()
    {
        $this->cache->reset();
        $this->assertHeaders(array());
    }

    public function testDisable()
    {
        $this->cache->disable();
        $this->assertHeaders(array(
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Mon, 01 Jan 0001 00:00:00 GMT',
        ));
    }

    public function testSetAge()
    {
        $this->cache->setAge('88');
        $this->assertHeaders(array(
            'Age' => '88',
        ));
    }

    public function testEtag()
    {
        $this->cache->setEtag('foobar');
        $this->assertHeaders(array(
            'Etag' => '"foobar"',
        ));
        $this->cache->setWeakEtag('foobar');
        $this->assertHeaders(array(
            'Etag' => 'W/"foobar"',
        ));
    }

    public function testSetExpires()
    {
        $this->cache->setExpires('1979-11-07 +0000');
        $this->assertHeaders(array(
            'Expires' => 'Wed, 07 Nov 1979 00:00:00 GMT',
        ));

        // try with a DateTime object
        $date = new DateTime('1970-08-08 +0000');
        $this->cache->setExpires($date);
        $this->assertHeaders(array(
            'Expires' => 'Sat, 08 Aug 1970 00:00:00 GMT',
        ));

        // try setting a bad date
        $this->cache->setExpires('I am not a date');
        $this->assertHeaders(array(
            'Expires' => 'Mon, 01 Jan 0001 00:00:00 GMT',
        ));
    }

    public function testLastModified()
    {
        $this->cache->setLastModified('1979-11-07 +0000');
        $this->assertHeaders(array(
            'Last-Modified' => 'Wed, 07 Nov 1979 00:00:00 GMT',
        ));
    }

    public function testSetMaxAge()
    {
        $this->cache->setMaxAge(88);
        $this->assertHeaders(array(
            'Cache-Control' => 'max-age=88'
        ));
    }

    public function testSetNoCache()
    {
        $this->cache->setNoCache();
        $this->assertHeaders(array(
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
        ));
    }

    public function testSetNoStore()
    {
        $this->cache->setNoStore();
        $this->assertHeaders(array(
            'Cache-Control' => 'no-store',
        ));
    }

    public function testSetPrivateAndPublic()
    {
        $this->cache->setPrivate();
        $this->assertHeaders(array(
            'Cache-Control' => 'private',
        ));

        $this->cache->setPublic();
        $this->assertHeaders(array(
            'Cache-Control' => 'public',
        ));
    }

    public function testSetSharedMaxAge()
    {
        $this->cache->setSharedMaxAge(88);
        $this->assertHeaders(array(
            'Cache-Control' => 'public, s-maxage=88',
        ));
    }

    public function testSetVary()
    {
        $this->cache->setVary(array('foo', 'bar'));
        $this->assertHeaders(array(
            'Vary' => 'foo, bar',
        ));
    }
}
