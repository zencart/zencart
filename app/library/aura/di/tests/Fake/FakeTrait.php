<?php
namespace Aura\Di\Fake;

trait FakeTrait
{
    use FakeChildTrait;

    protected $fake;

    public function setFake($fake)
    {
        $this->fake = $fake;
    }

    public function getFake()
    {
        return $this->fake;
    }
}