<?php
namespace Aura\Di\Fake;

class FakeMeldingClass
{
    protected $foo;

    public function __invoke(FakeMalleableClass $object)
    {
        $object->setFoo('baz');
        return $object;
    }
}
