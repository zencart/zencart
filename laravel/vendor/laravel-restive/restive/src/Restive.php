<?php declare(strict_types=1);

namespace Restive;

use Restive\Http\Routing\ResourceRegistrar;
use Restive\Http\Routing\PendingResourceRegistration;

class Restive
{
    public function resource(string $name, string $controller, array $options = []): PendingResourceRegistration
    {
        $registrar = $this->resolveRegistrar(ResourceRegistrar::class);
        return new PendingResourceRegistration($registrar, $name, $controller, $options);
    }

    protected function resolveRegistrar(string $registrarClass): ResourceRegistrar
    {
        return new $registrarClass(app('router'));
    }
}
