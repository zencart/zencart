<?php
namespace Aura\Di\Fake;

trait FakeChildTrait
{
    use FakeGrandchildTrait;

    protected $child_fake;

    public function setChildFake($fake)
    {
        $this->child_fake = $fake;
    }

    public function getChildFake()
    {
        return $this->child_fake;
    }
}
