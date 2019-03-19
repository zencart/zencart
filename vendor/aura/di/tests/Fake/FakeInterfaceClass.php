<?php
namespace Aura\Di\Fake;

class FakeInterfaceClass implements FakeInterface
{
    protected $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;
        return $this;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
