<?php

namespace Restive\Routing;

use Illuminate\Routing\Route;

class ResourceRegistrar extends \Illuminate\Routing\ResourceRegistrar
{
    protected function addResourceDestroy($name, $base, $controller, $options): Route
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'?}/';
        $action = $this->getResourceAction($name, $controller, 'destroy', $options);
        return $this->router->delete($uri, $action);
    }

    protected function addResourceUpdate($name, $base, $controller, $options): Route
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'?}/';
        $action = $this->getResourceAction($name, $controller, 'update', $options);
        return $this->router->put($uri, $action);
    }
}