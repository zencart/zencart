<?php
namespace Aura\Di\Fake;

class FakeChildClass extends FakeParentClass
{
    protected $zim;

    protected $fake;

    public function __construct($foo, $zim = null)
    {
        parent::__construct($foo);
        $this->zim = $zim;
    }

    public function setFake($fake)
    {
        $this->fake = $fake;
    }

    public function getFake()
    {
        return $this->fake;
    }

    public function getZim()
    {
        return $this->zim;
    }
}
