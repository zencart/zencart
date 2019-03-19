<?php
namespace Aura\Di\Fake;

class FakeMalleableClass
{
    protected $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
