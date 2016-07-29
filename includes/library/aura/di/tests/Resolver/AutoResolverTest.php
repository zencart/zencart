<?php
namespace Aura\Di\Resolver;

use Aura\Di\Injection\LazyNew;

class AutoResolverTest extends ResolverTest
{
    protected $resolver;

    protected function setUp()
    {
        parent::setUp();
        $this->resolver = new AutoResolver(new Reflector());
    }

    public function testMissingParam()
    {
        $actual = $this->resolver->resolve('Aura\Di\Fake\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeParentClass', $actual->params['fake']);
    }

    public function testAutoResolveExplicit()
    {
        $this->resolver->types['Aura\Di\Fake\FakeParentClass'] = new LazyNew($this->resolver, 'Aura\Di\Fake\FakeChildClass');
        $actual = $this->resolver->resolve('Aura\Di\Fake\FakeResolveClass');
        $this->assertInstanceOf('Aura\Di\Fake\FakeChildClass', $actual->params['fake']);
    }

    public function testAutoResolveMissingParam()
    {
        $this->setExpectedException('Aura\Di\Exception\MissingParam');
        $actual = $this->resolver->resolve('Aura\Di\Fake\FakeParamsClass');
    }
}
