<?php
namespace Aura\Di\Fake;

class FakeParentClass
{
    protected $foo;

    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function mirror($value)
    {
        return $value;
    }
}
