<?php
namespace Aura\Web\Response;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    protected $cache;

    protected $headers;

    protected $status;

    protected function setUp()
    {
        $this->status   = new Status;
        $this->headers  = new Headers;
        $this->cache    = new Cache($this->headers);
        $this->redirect = new Redirect(
            $this->status,
            $this->headers,
            $this->cache
        );
    }

    protected function assertHeaders(array $expect)
    {
        $actual = $this->headers->get();
        $this->assertSame($expect, $actual);
    }

    public function testTo()
    {
        $this->redirect->to('http://example.com', '201', 'Created Phrase');
        $this->assertSame(201, $this->status->getCode());
        $this->assertSame('Created Phrase', $this->status->getPhrase());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
        ));
    }

    public function testAfterPost()
    {
        $this->redirect->afterPost('http://example.com');
        $this->assertSame(303, $this->status->getCode());
        $this->assertSame('See Other', $this->status->getPhrase());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Mon, 01 Jan 0001 00:00:00 GMT',
        ));
    }

    public function testCreated()
    {
        $this->redirect->created('http://example.com');
        $this->assertSame(201, $this->status->getCode());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
        ));
    }

    public function testMovedPermanently()
    {
        $this->redirect->movedPermanently('http://example.com');
        $this->assertSame(301, $this->status->getCode());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
        ));
    }

    public function testFound()
    {
        $this->redirect->found('http://example.com');
        $this->assertSame(302, $this->status->getCode());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
        ));
    }

    public function testSeeOther()
    {
        $this->redirect->seeOther('http://example.com');
        $this->assertSame(303, $this->status->getCode());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate, proxy-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Mon, 01 Jan 0001 00:00:00 GMT',
        ));
    }

    public function testTemporaryRedirect()
    {
        $this->redirect->temporaryRedirect('http://example.com');
        $this->assertSame(307, $this->status->getCode());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
        ));
    }

    public function testPermanentRedirect()
    {
        $this->redirect->permanentRedirect('http://example.com');
        $this->assertSame(308, $this->status->getCode());
        $this->assertHeaders(array(
            'Location' => 'http://example.com',
        ));
    }
}
