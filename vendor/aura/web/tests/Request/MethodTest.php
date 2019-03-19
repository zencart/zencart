<?php
namespace Aura\Web\Request;

class MethodTest extends \PHPUnit_Framework_TestCase
{
    protected function newMethod($server = array(), $post = array())
    {
        return new Method($server, $post);
    }

    public function test__call()
    {
        $server['REQUEST_METHOD'] = 'OTHER';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isOther());

        $this->setExpectedException('BadMethodCallException');
        $method->badMethodCall();
    }

    public function testIsGet()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isGet());

        $server['REQUEST_METHOD'] = 'GET';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isGet());

        $server['REQUEST_METHOD'] = 'NOT-GET';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isGet());
    }

    public function testIsPost()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isPost());

        $server['REQUEST_METHOD'] = 'POST';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isPost());

        $server['REQUEST_METHOD'] = 'NOT-POST';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isPost());
    }

    public function testIsPut()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isPut());

        $server['REQUEST_METHOD'] = 'PUT';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isPut());

        $server['REQUEST_METHOD'] = 'NOT-PUT';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isPut());
    }

    public function testIsDelete()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isDelete());

        $server['REQUEST_METHOD'] = 'DELETE';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isDelete());

        $server['REQUEST_METHOD'] = 'NOT-DELETE';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isDelete());
    }

    public function testIsHead()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isHead());

        $server['REQUEST_METHOD'] = 'HEAD';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isHead());

        $server['REQUEST_METHOD'] = 'NOT-HEAD';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isHead());
    }

    public function testIsOptions()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isOptions());

        $server['REQUEST_METHOD'] = 'OPTIONS';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isOptions());

        $server['REQUEST_METHOD'] = 'NOT-OPTIONS';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isOptions());
    }

    public function testIsPatch()
    {
        $method = $this->newMethod();
        $this->assertFalse($method->isPatch());

        $server['REQUEST_METHOD'] = 'PATCH';
        $method = $this->newMethod($server);
        $this->assertTrue($method->isPatch());

        $server['REQUEST_METHOD'] = 'NOT-PATCH';
        $method = $this->newMethod($server);
        $this->assertFalse($method->isPatch());
    }

    public function testHttpMethodOverload()
    {
        // headers take precedence
        $server = array(
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT',
        );
        $post['_method'] = 'header-takes-precedence';
        $method = $this->newMethod($server, $post);
        $actual = $method->get();
        $this->assertSame('PUT', $actual);

        // no header? look for field name
        $server = array(
            'REQUEST_METHOD' => 'POST',
        );
        $post['_method'] = 'DELETE';
        $method = $this->newMethod($server, $post);
        $actual = $method->get();
        $this->assertSame('DELETE', $actual);
    }
}
