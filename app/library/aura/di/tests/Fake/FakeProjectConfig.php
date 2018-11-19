<?php
namespace Aura\Di\Fake;

use Aura\Di\Container;
use Aura\Di\ContainerConfig;

class FakeProjectConfig extends ContainerConfig
{
    public function define(Container $di)
    {
        parent::define($di);
        $di->set('project_service', (object) ['baz' => 'dib']);
    }

    public function modify(Container $di)
    {
        parent::modify($di);
        $di->get('project_service')->baz = 'gir';
    }
}
