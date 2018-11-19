<?php
namespace Aura\Web\_Config;

use Aura\Di\_Config\AbstractContainerTest;

class ContainerTest extends AbstractContainerTest
{
    protected function getConfigClasses()
    {
        return array(
            'Aura\Web\_Config\Common',
        );
    }

    protected function getAutoResolve()
    {
        return false;
    }

    public function provideGet()
    {
        return array(
            array('aura/web:response_headers', 'Aura\Web\Response\Headers'),
            array('aura/web:response_status', 'Aura\Web\Response\Status'),
            array('aura/web:response_cache', 'Aura\Web\Response\Cache'),
        );
    }

    public function provideNewInstance()
    {
        return array(
            array('Aura\Web\Request'),
            array('Aura\Web\Request\Client'),
            array('Aura\Web\Request\Content'),
            array('Aura\Web\Request\Globals'),
            array('Aura\Web\Request\Headers'),
            array('Aura\Web\Request\Method'),
            array('Aura\Web\Request\Url'),
            array('Aura\Web\Response'),
            array('Aura\Web\Response\Headers'),
            array('Aura\Web\Response\Content'),
            array('Aura\Web\Response\Cache'),
            array('Aura\Web\Response\Redirect'),
        );
    }
}
