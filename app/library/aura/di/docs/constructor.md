# Constructor Injection

When we use the _Container_ to instantiate a new object, we often need to inject (i.e., set) constructor parameter values in various ways.

We can define default values for constructor parameters using the `$di->params` array on the _Container_ before locking it.

Let's look at a class that takes some constructor parameters:

```php
namespace Vendor\Package;

class Example
{
    protected $foo;
    protected $bar;
    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
```

If we were to try to create an object using `$di->newInstance('Vendor\Package\Example')`, the instantiation would fail. The `$foo` and `$bar` params are required, and the _Container_ does not know what to use for that value.

To remedy this, we tell the _Container_ what values to use for each _Vendor\Package\Example_ constructor parameter by name using the `$di->params` array:

```php
$di->params['Vendor\Package\Example']['foo'] = 'foo_value';
$di->params['Vendor\Package\Example']['bar'] = 'bar_value';
```

We can also specify by position:

```php
$di->params['Vendor\Package\Example'][0] = 'foo_value';
$di->params['Vendor\Package\Example'][1] = 'bar_value';
```

Once all the params are set, we create an object with `$di->newInstance('Vendor\Package\Example')`, the instantiation will work correctly. Each time we create an instance through the _Container_, it will apply the `$di->params` values for the matching class.

> N.b.: If you try to access `$params` after calling `newInstance()` (or after locking the _Container_ using the `lock()` method) the _Container_ will throw an exception. This is to prevent modifiying the params after objects have been created. Thus, be sure to set up all params for all objects before creating an object.
