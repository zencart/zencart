# Lazy Injection

Unlike the `newInstance()` and `get()` methods, calling one of the `lazy*()` methods on the _Container_ will *not* lock the _Container_. Using lazy injection is the preferred and recommended way to defines object values for `$params` and `$setters`, and to define services.

## Lazy Instances

Thus far, we have used `newInstance()` to create objects through the _Container_.
However, we usually don't want to create an object *right at that moment* when
defining it. Instead, we almost always want to wait to create the object only
at the time it is actually needed. Using `lazyNew()` instead allows us to defer object
creation until it is needed as a dependency for another object.

```php
$di->params['Example']['foo'] = $di->lazyNew('AnotherExample');
```

Now the _AnotherExample_ object will only be instantiated when the _Example_ object
is instantiated. It will use the default _AnotherExample_ injection values.

If we want to override the default `$di->params` values for a specific lazy instance, we can pass a `$params` array as the second argument to `lazyNew()` to merge with the default values. For example:

```php
$di->set('service_name', $di->lazyNew(
    'AnotherExample',
    [
        'bar' => 'alternative_bar_value',
    ]
));
```

This will leave the `$foo` parameter default in place, and override the `$bar` parameter value, for just that instance of the _ExampleWithParams_.

Likewise, we can note instance-specific setter values to use in place of the defaults. We do so via the third argument to `$di->lazyNew()`. For example:

```php
$di->set('service_name', $di->lazyNew(
    'AnotherExample',
    [], // no $params overrides
    [
        'setFoo' => 'alternative_foo_value',
    ]
));
```

## Lazy Services

### Setting

As with object instances, we generally want to create a service instance only at the moment we *get* it, not at the moment we *set* it. To lazy-load a service, set the service using the `lazyNew()` method:

```php
// set the service as a lazy-loaded new instance
$di->set('service_name', $di->lazyNew('Example'));
```

Now the service is created only when we we `get()` it, and not before. This lets us set as many services as we want, but only incur the overhead of creating the instances we actually use.

### Getting

Sometimes a class will need a service as one of its parameters. By way of example, the following class needs a database connection:

```php
class ExampleNeedsService
{
    protected $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
}
```

To inject a shared service as a parameter value, use `$di->lazyGet()` so that the service object is not created until the _ExampleNeedsService_ object is created:

```php
$di->params['ExampleNeedsService']['db'] = $di->lazyGet('db_service');
```

This keeps the service from being created until the very moment it is needed. If we never instantiate anything that needs the service, the service itself will never be instantiated.

### Getting-and-Calling

Sometimes it will be useful to retrieve the result of a method call to a shared service. To do so, use the `lazyGetCall()` method, passing the name of the service first, followed by the method name, and optionally followed by any arguments to the method.

```php
$di->params['ExampleNeedsFactoriedObject']['object'] = $di->lazyGetCall('factory_service', 'newInstance');
```


## Lazy Values

Sometimes we know that a parameter needs to be specified, but we don't know what it will be until later.  Perhaps it is the result of looking up an API key from an environment variable. In these and other cases, we can tell a constructor parameter or setter method to use a "lazy value" and then specify that value elsewhere.

For example, we can configure the _Example_ constructor parameters to use lazy values like so:

```php
$di->params['Example']['foo'] = $di->lazyValue('fooval');
$di->params['Example']['bar'] = $di->lazyValue('barval');
```

We can then specify at some later time the values of `fooval` and `barval` using the `$di->values` array:

```php
$di->values['fooval'] = 'lazy value for foo';
$di->values['barval'] = 'lazy value for bar';
```

## Lazy Include and Require

Occasionally we will need to `include` a file that returns a value, such as data file that returns a PHP array:

```php
// /path/to/data.php
return [
    'foo' => 'bar',
    'baz' => 'dib',
    'zim' => 'gir'
];
```

We could set a constructor parameter or setter method value to `include "/path/to/data.php"`, but that would cause the file to be read filesystem at that moment, instead of at instantiation time.  To lazy-load a file as a value, call `$di->lazyInclude()` or `$di->lazyRequire()` (depending on your preference for warning levels).

```php
$di->params['Example1']['data'] = $di->lazyInclude('/path/to/data.php');
$di->params['Example2']['data'] = $di->lazyRequire('/path/to/data.php');
```

## Generic Lazy Calls

It may be that we have a complex bit of logic we need to execute for a value. If none of the existing `$di->lazy*()` methods meet our needs, we can wrap an anonymous function or other callable in a `lazy()` call, and the callable's return will be used as the value.

```php
$di->params['Example']['foo'] = $di->lazy(function () {
    // complex calculations, and then:
    return $result;
});
```

Note that this will work with any PHP callable, such as a static method or instance method.

```php
// ServiceClass::staticMethod()
$di->set('service', $di->lazy(['ServiceClass', 'staticMethod']));

// or an instance of $serviceClass->methodName()
$instance = $di->newInstance('ServiceClass');
$di->set('service', $di->lazy([$instance, 'methodName']));
```

You can pass arguments to the callable like so:

```php
// ServiceClass::staticMethod($arg1, $arg2, $arg3)
$di->set('service', $di->lazy(['ServiceClass', 'staticMethod'],
    $arg1,
    $arg2,
    $arg3
));
```

Lazies in the callable array, and in the arguments, will be resolved automatically.

```php
// $serviceClass->methodName($configArray)
$di->set('service', $di->lazy([$di->lazyNew('ServiceClass'), 'methodName'],
    $di->lazyInclude('/path/to/config.array.php')
));
```

Beware of relying on generic Lazy calls too much; if we do, it probably means we need to separate our configuration concerns better than we are currently doing.
