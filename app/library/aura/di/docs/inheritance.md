# Class, Interface, and Trait Inheritance

> N.b.: When specifying fully-qualified class names, **do not** include the leading namespace separator. Doing so may lead to unexpected behavior. In other words, always use `ClassName` and never use `\ClassName`.

## Class Inheritance

Each class instantiated through the _Container_ "inherits" the constrctor parameter and setter method values of its parents by default. This means we can specify a constructor parameter or setter method value on a parent class, and the child class will use it (that is, unless we set an overriding value on the child class).

Let's say we have this parent class and this child class:

```php
class ExampleParent
{
    protected $foo;
    protected $bar;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}

class ExampleChild extends ExampleParent
{
    protected $baz;

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }
}
```

If we define the constructor parameters and setter method values for the parent ...

```php
$di->params['ExampleParent']['foo'] = 'parent_foo';
$di->setters['ExampleParent']['setBar'] = 'parent_bar';
```

... then when we call `$di->newInstance('ExampleChild')`, the child class will "inherit" those values as defaults.

We can always override the "inherited" values by specifying them for the child class directly:

```php
$di->params['ExampleChild']['foo'] = 'child_foo';
$di->setters['ExampleChild']['setBaz'] = 'child_baz';
```

Classes extended from the child class will then inherit those new values. In this way, constructor parameter and setter method values are propagated down the inheritance hierarchy.

## Interface And Trait Inheritance

If a class exposes a setter method by implementing an interface or using a trait, we can specify the default value for that setter method on the interface or trait. That value will then be applied by default to every class that extends that implements that interface or uses that trait.

For example, let's say we have this interface, trait, and class:

```php
interface ExampleBarInterface
{
    public function setBar($bar);
}

trait ExampleFooTrait
{
    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}

class Example implements ExampleBarInterface
{
    use ExampleFooTrait;

    protected $foo;
    protected $bar;

    public function setBar($bar)
    {
        $this->bar = $bar;
    }
}
```

We can define the default setter method values on the trait and interface:

```php
$di->setters['ExampleFooTrait']['setFoo'] = 'foo_value';
$di->setters['ExampleBarInterface']['setBar'] = 'bar_value';
```

When we call `$di->newInstance('Example')`, those setter methods will be called by the _Container_ with those values.

Note that if we have class-specific `$di->setter` values, those will take precedence over the trait and interface setter values.
