<?php
namespace Aura\Di\Fake;

class FakeInvokableClass
{
    protected $foo;

    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }

    public function __invoke($value)
    {
        return $this->foo . $value;
    }
}
