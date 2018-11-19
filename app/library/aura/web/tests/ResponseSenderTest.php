<?php
namespace Aura\Web;

class ResponseSenderTest extends \PHPUnit_Framework_TestCase
{
    protected $response;

    protected $response_sender;

    protected function setUp()
    {
        parent::setUp();
        $globals = array();
        $factory = new WebFactory($globals);
        $this->response = $factory->newResponse();
        $this->response_sender = new FakeResponseSender($this->response);
        FakeResponseSender::reset();
    }

    public function testStringContent()
    {
        $this->response->status->set(299, 'Doom');
        $this->response->headers->set('Foo', 'Bar');
        $this->response->cookies->set('Baz', 'Dib');
        $this->response->content->set("Hello World!");
        $this->response_sender->__invoke();

        $expect = array(
            array(
                0 => 'HTTP/1.1 299 Doom',
                1 => true,
                2 => 299,
            ),
            array(
                0 => 'Foo: Bar',
                1 => false,
            ),
        );
        $this->assertSame($expect, FakeResponseSender::$headers);

        $expect = array(
            array(
                0 => 'Baz',
                1 => 'Dib',
                2 => 0,
                3 => '',
                4 => '',
                5 => false,
                6 => true,
            ),
        );

        $this->assertSame($expect, FakeResponseSender::$cookies);

        $this->assertSame("Hello World!", FakeResponseSender::$content);
    }

    public function testClosureContent()
    {
        $this->response->content->set(function () {
            echo "Hello World!";
        });

        $this->response_sender->__invoke();

        $this->assertSame("Hello World!", FakeResponseSender::$content);
    }
}
