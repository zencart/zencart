<?php
namespace Aura\Web\Response;

class CookiesTest extends \PHPUnit_Framework_TestCase
{
    protected $cookies;

    protected function setUp()
    {
        $this->cookies = new Cookies;
    }

    public function testSetAndGet()
    {
        $this->cookies->set('foo', 'bar', '88', '/path', 'example.com');

        $expect = array(
          'value' => 'bar',
          'expire' => 88,
          'path' => '/path',
          'domain' => 'example.com',
          'secure' => false,
          'httponly' => true,
        );

        $actual = $this->cookies->get('foo');

        $this->assertSame($expect, $actual);
    }

    public function testGetAll()
    {
        $this->cookies->set('foo', 'bar', '88', '/path', 'example.com');
        $this->cookies->set('baz', 'dib', date('Y-m-d H:i:s', '88'), '/path', 'example.com');

        $expect = array(
            'foo' => array(
              'value' => 'bar',
              'expire' => 88,
              'path' => '/path',
              'domain' => 'example.com',
              'secure' => false,
              'httponly' => true,
            ),
            'baz' => array(
              'value' => 'dib',
              'expire' => 88,
              'path' => '/path',
              'domain' => 'example.com',
              'secure' => false,
              'httponly' => true,
            ),
        );

        $actual = $this->cookies->get();

        $this->assertSame($expect, $actual);
    }

    public function testDefault()
    {
        // set a cookie name and value
        $this->cookies->set('foo', 'bar');

        // get before defaults
        $expect = array(
            'value' => 'bar',
            'expire' => 0,
            'path' => '',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
        );
        $actual = $this->cookies->get('foo');
        $this->assertSame($expect, $actual);

        // set and get defaults
        $this->cookies->setExpire(88);
        $this->cookies->setPath('/path');
        $this->cookies->setDomain('example.com');
        $this->cookies->setSecure(true);
        $this->cookies->setHttponly(false);

        // get after defaults
        $expect = array(
          'value' => null,
          'expire' => 88,
          'path' => '/path',
          'domain' => 'example.com',
          'secure' => true,
          'httponly' => false,
        );
        $actual = $this->cookies->getDefault();
        $this->assertSame($expect, $actual);
    }

}
