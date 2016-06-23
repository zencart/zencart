# Container Builder and Config Classes

The _ContainerBuilder_ also builds fully-configured _Container_ objects using _ContainerConfig_ classes. It works using a [two-stage configuration system](http://auraphp.com/blog/2014/04/07/two-stage-config/).

The two stages are "define" and "modify". In the "define" stage, the _ContainerConfig_ object defines constructor parameter values, setter method values, services, and so on. The _ContainerBuilder_ then locks the _Container_ so that these definitions cannot be changed, and begins the "modify" stage. In the "modify" stage, we may `get()` services from the _Container_ and modify them programmatically if needed.

To build a fully-configured _Container_ using the _ContainerBuilder_, we do something like the following:

```php
use Aura\Di\ContainerBuilder;

$container_builder = new ContainerBuilder();

// use the builder to create and configure a container
// using an array of ContainerConfig classes
$di = $container_builder->newConfiguredInstance([
    'Aura\Cli\_Config\Common',
    'Aura\Router\_Config\Common',
    'Aura\Web\_Config\Common',
]);
```

A configuration class looks like the following:

```php
namespace Vendor\Package;

use Aura\Di\Container;
use Aura\Di\ContainerConfig;

class Config extends ContainerConfig
{
    public function define(Container $di)
    {
        $di->set('log_service', $di->lazyNew('Logger'));
        $di->params['Logger']['dir'] = '/path/to/logs';
    }

    public function modify(Container $di)
    {
        $log = $di->get('log_service');
        $log->debug('Finished config.');
    }
}
```

Here are some example _ContainerConfig_ classes from earlier Aura packages:

- [Aura.Cli](https://github.com/auraphp/Aura.Cli/blob/2.0.0/config/Common.php)
- [Aura.Html](https://github.com/auraphp/Aura.Html/blob/2.0.0/config/Common.php)
- [Aura.Router](https://github.com/auraphp/Aura.Router/blob/2.0.0/config/Common.php)
- [Aura.View](https://github.com/auraphp/Aura.View/blob/2.0.0/config/Common.php)

Alterntively, if you already have a ContainerConfig object created, you can pass it directly to the ContainerBuilder instead of a string class name:

```php
$routerConfig = new Aura\Router\_Config\Common();

// use the builder to create and configure a container
// using an array of ContainerConfig classes
$di = $container_builder->newConfiguredInstance([
    'Aura\Cli\_Config\Common',
    $routerConfig,
    'Aura\Web\_Config\Common',
]);
```
