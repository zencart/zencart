# Setter Injection

The _Container_ supports setter injection in addition to constructor injection. (These can be combined as needed.)

After the _Container_ constructs a new instance of an object, we can specify that certain methods should be called with certain values immediately after instantiation by using the `$di->setter` array before locking it.

Say we have class like the following:

```php
namespace Vendor\Package;

class Example
{
    protected $foo;

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}
```

We can specify that, by default, the `setFoo()` method should be called with a specific value after construction like so:

```php
$di->setters['Vendor\Package\Example']['setFoo'] = 'foo_value';
```

Note also that this works only with explicitly-defined setter methods. Setter methods that exist only via magic `__call()` will not be honored.

> N.b.: If you try to access `$setters` after calling `newInstance()` (or after locking the _Container_ using the `lock()` method) the _Container_ will throw an exception. This is to prevent modifiying the params after objects have been created. Thus, be sure to set up all params for all objects before creating an object.
