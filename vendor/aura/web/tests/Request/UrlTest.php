<?php
namespace Aura\Web\Request;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    protected function newUrl($server = array())
    {
        return new Url($server);
    }

    /**
     * @dataProvider serverProvider
     */
    public function testGet($expect, $server)
    {
        $url = $this->newUrl($server);

        $actual = $url->get();
        $this->assertSame($expect, $actual);

        $expect = '/foo';
        $actual = $url->get(PHP_URL_PATH);
        $this->assertSame($expect, $actual);

        $this->setExpectedException('Aura\Web\Exception');
        $url->get('no such component');
    }

    public function testisSecure()
    {
        $url = $this->newUrl();
        $this->assertFalse($url->isSecure());

        $server = array('HTTPS' => 'on');
        $url = $this->newUrl($server);
        $this->assertTrue($url->isSecure());

        $server = array('SERVER_PORT' => '443');
        $url = $this->newUrl($server);
        $this->assertTrue($url->isSecure());
    }

    public function serverProvider()
    {
        return array(
            array(
                'http://example.com/foo?bar=baz',
                array(
                    'HTTP_HOST'   => 'example.com',
                    'REQUEST_URI' => '/foo?bar=baz'
                )
            ),
            array(
                'http://example.com:1180/foo?bar=baz',
                array(
                    'HTTP_HOST'   => 'example.com',
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '1180',
                    'REQUEST_URI' => '/foo?bar=baz'
                )
            ),
            array(
                'http://example.com:1180/foo?bar=baz',
                array(
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '1180',
                    'REQUEST_URI' => '/foo?bar=baz'
                )
            ),
            array(
                'http://example.com:1180/foo?bar=baz',
                array(
                    'HTTP_HOST'   => 'example.com:1180',
                    'REQUEST_URI' => '/foo?bar=baz'
                )
            ),
            array(
                'http://example.com:1180/foo?bar=baz',
                array(
                    'SERVER_NAME' => 'example.com',
                    'SERVER_PORT' => '1180',
                    'REQUEST_URI' => '/foo?bar=baz'
                )
            )
        );
    }
}
