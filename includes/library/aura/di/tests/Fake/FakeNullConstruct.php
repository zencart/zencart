<?php

namespace Aura\Di\Fake;

class FakeNullConstruct
{
    public function __construct($foo = 'bar')
    {
        if (!is_null($foo)) {
            throw new \InvalidArgumentException('Must receive null');
        }
    }
}
