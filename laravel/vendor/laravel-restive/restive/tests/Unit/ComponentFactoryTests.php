<?php


namespace Tests\Unit;

use Restive\ApiQueryParser;
use Restive\ComponentFactory;
use Restive\ParserFactory;
use Tests\Fixtures\App\Http\Controllers\Api\Dummy1Controller;
use Tests\Fixtures\App\Http\Controllers\Api\DummyController;
use Tests\TestCase;
use Tests\Fixtures\App\Http\Requests\DummyRequest;

class ComponentFactoryTests extends TestCase
{
    /** @test */
    public function resolves_request_class_from_fqn()
    {
        $cf = new ComponentFactory();
        $cf->setRequestNamespacePrefix('Tests\\Fixtures');
        $cf->setModelNamespacePrefix('Tests\\Fixtures');
        $controller = new Dummy1Controller(new ApiQueryParser(new ParserFactory()), $cf);
        $request = $controller->getRequest();
        $this->assertEquals(DummyRequest::class, $request);
    }

    /** @test */
    public function resolves_request_class_from_empty_name()
    {
        $cf = new ComponentFactory();
        $cf->setRequestNamespacePrefix('Tests\\Fixtures');
        $cf->setModelNamespacePrefix('Tests\\Fixtures');
        $controller = new DummyController(new ApiQueryParser(new ParserFactory()), $cf);
        $request = $controller->getRequest();
        $this->assertEquals(DummyRequest::class, $request);
    }
}