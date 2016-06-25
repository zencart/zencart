<?php
namespace Aura\Di;

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;

    protected function setUp()
    {
        parent::setUp();
        $this->builder = new ContainerBuilder();
    }

    public function testAutoResolverInstance()
    {
        $builder = new ContainerBuilder();
        $container = $builder->newInstance(ContainerBuilder::AUTO_RESOLVE);
        $resolver = $container->getInjectionFactory()->getResolver();
        $this->assertInstanceOf('Aura\Di\Resolver\AutoResolver', $resolver);
    }

    public function testNewConfiguredInstance()
    {
        $config_classes = [
            'Aura\Di\Fake\FakeLibraryConfig',
            'Aura\Di\Fake\FakeProjectConfig',
        ];

        $di = $this->builder->newConfiguredInstance($config_classes);

        $this->assertInstanceOf('Aura\Di\Container', $di);

        $expect = 'zim';
        $actual = $di->get('library_service');
        $this->assertSame($expect, $actual->foo);

        $expect = 'gir';
        $actual = $di->get('project_service');
        $this->assertSame($expect, $actual->baz);
    }

    public function testNewConfiguredInstanceViaObject()
    {
        $config_classes = [
            new \Aura\Di\Fake\FakeLibraryConfig,
            new \Aura\Di\Fake\FakeProjectConfig,
        ];

        $di = $this->builder->newConfiguredInstance($config_classes);

        $this->assertInstanceOf('Aura\Di\Container', $di);

        $expect = 'zim';
        $actual = $di->get('library_service');
        $this->assertSame($expect, $actual->foo);

        $expect = 'gir';
        $actual = $di->get('project_service');
        $this->assertSame($expect, $actual->baz);
    }

    public function testInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        $di = $this->builder->newConfiguredInstance([[]]);
    }

    public function testInvalidDuckType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $di = $this->builder->newConfiguredInstance([(object) []]);
    }

    public function testSerializeAndUnserialize()
    {
        $di = $this->builder->newInstance();

        $di->params['Aura\Di\Fake\FakeParamsClass'] = [
            'array' => [],
            'empty' => 'abc'
        ];

        $instance = $di->newInstance('Aura\Di\Fake\FakeParamsClass');

        $this->assertInstanceOf('Aura\Di\Fake\FakeParamsClass', $instance);

        $serialized = serialize($di);
        $unserialized = unserialize($serialized);

        $instance = $unserialized->newInstance('Aura\Di\Fake\FakeParamsClass', [
            'array' => ['a' => 1]
        ]);

        $this->assertInstanceOf('Aura\Di\Fake\FakeParamsClass', $instance);
    }
}
