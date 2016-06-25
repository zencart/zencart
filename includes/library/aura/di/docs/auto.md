# Auto-Resolution Of Constructor Parameters

Some developers prefer to let the _Container_ resolve dependencies on its own, without having to specify anything in a configuration file. Note that there can be unusual debugging problems inherent in tracking down the default injections, so auto-resolution may or may not be your preference.

To use auto-resolution in a _Container_, build the _Container_ with `$container = $builder->newInstance($builder::AUTO_RESOLVE)`.

Note that auto-resolution only works for class/interface typehints. It does not work for `array` typehints.

Note also that auto-resolution does not apply to setter methods. This is because the _Container_ does not know which methods are setters and which are "normal use" methods. Since you have to specify `$di->setters` anyway, the _Container_ has no chance to attempt auto-resolution.

## Auto-Resolving From Concrete Typehints

If the parameter is typehinted to a class but there is no `$di->params` value for that parameter, and also no default value, the _Container_ will automatically fill in a `lazyNew()` call to the typehinted class.

For example, look at the following class; it has  a parameter typehinted to a class, and no default value:

```php
class Example
{
    public function __construct(Foo $foo)
    {
        // ...
    }
}
```

The _Container_ will auto-resolve the constructor param as if you had explicitly specified the following:

```php
$di->params['Example']['foo'] = $di->lazyNew('Foo');
```

## Auto-Resolving From Abstract and Interface Typehints

Obviously, you can't instantiate an interface or an abstract class. So, if a constructor parameter is typehinted like this ...

```php
class Example
{
    public function __construct(FooInterface $foo)
    {
        // ...
    }
}
```

... the _Container_ cannot auto-resolve with `lazyNew('FooInterface')`.

When it comes to interfaces and abstracts, then, you have to tell the _Container_ how to resolve them using `$di->types`:

```php
$di->types['FooInterface'] = $di->lazyNew('Foo');
```

The _Container_ will now resolve all _FooInterface_ typehints to a lazy-new instance of _Foo_.

## Auto-Resolving to Services

Sometimes you don't want a new instance of the typehinted param. Often, you will want to use a service instead.  Auto-resolving a typehint to a service is easy, using the `$di->types` technique from above.  For example, given this class ...

```php
class Example
{
    public function __construct(DbInterface $db)
    {
        // ...
    }
}
```

... we can auto-resolve all _DbInterface_ typehints to a service in the _Container_:

```
$di->types['DbInterface'] = $di->lazyGet('database_connection');
```

This works for concrete classes as well. Given this class:

```php
class Example
{
    public function __construct(Db $db)
    {
        // ...
    }
}
```

... we can auto-resolve all concrete _PDO_ typehints to a service in the _Container_:

```
$di->types['Db'] = $di->lazyGet('database_connection');
```

## Overriding Auto-Resolution

You may wish to explicitly specify a constructor parameter for a class, instead of letting the _Container_ use auto-resolution. You can do so through the old familiar `$di->params` technique.

```
// by default, resolve to this service
$di->types['DbInterface'] = $di->lazyGet('database_connection');

// but in this particular class, and its children,
// resolve to this other service
$di->params['OtherClass']['db'] = $di->lazyGet('other_connection');
```
