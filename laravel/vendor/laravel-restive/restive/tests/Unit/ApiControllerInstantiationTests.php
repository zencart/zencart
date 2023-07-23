<?php


namespace Tests\Unit;

use Restive\ApiQueryParser;
use Restive\ComponentFactory;
use Restive\Exceptions\InvalidModelException;
use Restive\ParserFactory;
use Tests\Fixtures\App\Http\Controllers\Api\DummyController;
use Tests\Fixtures\App\Http\Controllers\Api\Dummy1Controller;
use Tests\Fixtures\App\Http\Controllers\Api\Dummy2Controller;
use Tests\Fixtures\App\Http\Controllers\Api\Dummy3Controller;
use Tests\Fixtures\App\Http\Controllers\Api\UserController;
use Tests\TestCase;

class ApiControllerInstantiationTests extends TestCase
{
    /** @test */
    public function tests_instantiation_custom_namespace()
    {
        $controller = new UserController(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
        $this->assertInstanceOf(UserController::class, $controller);
    }

    /** @test */
    public function tests_instantiation_app_namespace()
    {
        $cf = new ComponentFactory();
        $cf->setModelNamespacePrefix('\\Tests\\Fixtures');
        $cf->setResourceNamespacePrefix('\\Tests\\Fixtures');
        $cf->setResourceCollectionNamespacePrefix('\\Tests\\Fixtures');
        $controller = new DummyController(new ApiQueryParser(new ParserFactory()), $cf);
        $this->assertInstanceOf(DummyController::class, $controller);
    }

    /** @test */
    public function tests_instantiation_app_models_namespace()
    {
        $cf = new ComponentFactory();
        $cf->setModelNamespacePrefix('\\Tests\\Fixtures');
        $controller = new Dummy1Controller(new ApiQueryParser(new ParserFactory()), $cf);
        $this->assertInstanceOf(Dummy1Controller::class, $controller);
    }

    /** @test */
    public function tests_instantiation_invalid_model_namespace()
    {
        $this->expectException(InvalidModelException::class);
        new Dummy2Controller(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
   }

    /** @test */
    public function tests_instantiation_empty_model_namespace()
    {
        $this->expectException(InvalidModelException::class);
        new Dummy3Controller(new ApiQueryParser(new ParserFactory()), new ComponentFactory());
    }
}