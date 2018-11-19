<?php
namespace Aura\Di\Fake;

use Aura\Di\Container;
use Aura\Di\ContainerConfig;

class FakeLibraryConfig extends ContainerConfig
{
    public function define(Container $di)
    {
        parent::define($di);
        $di->set('library_service', (object) ['foo' => 'bar']);
    }

    public function modify(Container $di)
    {
        parent::modify($di);
        $di->get('library_service')->foo = 'zim';
    }
}
