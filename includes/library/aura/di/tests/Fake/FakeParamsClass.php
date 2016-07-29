<?php
namespace Aura\Di\Fake;

class FakeParamsClass
{
    public $array;
    public $empty = 'not null';
    public function __construct(array $array, $empty)
    {
        $this->array = $array;
        $this->empty = null;
    }
}
