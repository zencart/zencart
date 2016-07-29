# Services

A "service" is an object stored in the _Container_ under a unique name. Any time you `get()` the named service, you always get back the same object instance.

```php
// define the Example class
class Example
{
    // ...
}

// set services as lazy; if you use newInstance() it will lock the container
$di->set('service_name', $di->lazyNew('Example'));

// get a service; the first get() will lock the container
$service1 = $di->get('service_name');
$service2 = $di->get('service_name');

// the two service objects are the same
var_dump($service1 === $service2); // true
```

> N.b.: If you try to access `$params` or `$setters`, or to call `set()`, after calling `get()` or after locking the _Container_ using the `lock()` method, the _Container_ will throw an exception. This is to prevent modifiying the params after objects have been created. Thus, be sure to set up all params for all objects before creating an object.
