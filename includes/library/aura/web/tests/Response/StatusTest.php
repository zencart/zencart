<?php
namespace Aura\Web\Response;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    protected $status;

    protected function setUp()
    {
        $this->status = new Status;
    }

    public function testSetAndGet()
    {
        $this->status->set(404, 'Not Found', '1.0');
        $actual = $this->status->get();
        $expect = 'HTTP/1.0 404 Not Found';
        $this->assertSame($expect, $actual);
    }

    public function testSetAndGetCode()
    {
        $expect = 404;
        $this->status->setCode($expect);
        $actual = $this->status->getCode();
        $this->assertSame($expect, $actual);

        $this->status->setCode('555');
        $this->assertSame('', $this->status->getPhrase());
    }

    public function testSetCodeWrong()
    {
        $this->setExpectedException('Aura\Web\Exception\InvalidStatusCode');
        $this->status->setCode('88');
    }

    public function testSetAndGetPhrase()
    {
        $expect = 'Not Found';
        $this->status->setPhrase($expect);
        $actual = $this->status->getPhrase();
        $this->assertSame($expect, $actual);
    }

    public function testSetAndGetVersion()
    {
        $expect = 1.1;
        $this->status->setVersion($expect);
        $actual = $this->status->getVersion();
        $this->assertSame($expect, $actual);
    }

    public function testVersionWrong()
    {
        $this->setExpectedException('Aura\Web\Exception\InvalidVersion');
        $this->status->setVersion('88');
    }
}
