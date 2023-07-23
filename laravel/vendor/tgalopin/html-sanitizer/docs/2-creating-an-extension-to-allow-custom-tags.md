# Creating an extension to allow custom tags

If you want to use additional tags than the one present in the sanitizer core extensions, you can create your
own extension.

There are two steps in the creation of an extension: creating the node visitor which will handle the
custom tag and registering this visitor by creating an extension class.

To better understand how to create an extension suited to your needs, you can also have a look at the
[Image extension](https://github.com/tgalopin/html-sanitizer/tree/master/src/Extension/Image)
which shows the different features available.

## Creating a node and a node visitor

A node visitor is a class able to handle DOMNode instances of a certain type. It needs to implement the
`HtmlSanitizer\Visitor\VisitorInterface`.

A node visitor is responsible of adding a node to the tree of safe HTML by filtering the DOMNode
it's given. Thus, for an example `my-tag` custom tag, we need to create two classes: a Node and
a NodeVisitor.

The node could look like this:

```php
namespace App\Sanitizer;

use HtmlSanitizer\Node\AbstractTagNode;
use HtmlSanitizer\Node\HasChildrenTrait;

class MyTagNode extends AbstractTagNode
{
    use HasChildrenTrait; // Or IsChildlessTrait

    public function getTagName(): string
    {
        return 'my-tag';
    }
}
```

A simple visitor for a `my-tag` custom tag could look like this:

```php
namespace App\Sanitizer;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\HasChildrenNodeVisitorTrait;
use HtmlSanitizer\Visitor\NamedNodeVisitorInterface;

class MyTagNodeVisitor extends AbstractNodeVisitor implements NamedNodeVisitorInterface
{
    use HasChildrenNodeVisitorTrait; // Or IsChildlessTagVisitorTrait

    protected function getDomNodeName(): string
    {
        return 'my-tag';
    }

    public function getDefaultAllowedAttributes(): array
    {
        return [
            'class', 'width', 'height'
        ];
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'custom_config' => null,
        ];
    }

    protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface
    {
        // You need to pass the current node as your node parent
        $node = new MyTagNode($cursor->node);
        
        // You can use $this->config['custom_config'] to access the user-defined configuration

        return $node;
    }
}
```

## Registering the node visitor with an extension

Once you created a node and a node visitor, you need to use an extension to register the visitor in the
sanitizer.

An extension is a class implementing the `HtmlSanitizer\Extension\ExtensionInterface` interface, which requires
two methods:

- `getName()` which should return the name to use in the configuration (`basic`, `list`, etc.) ;
- and `createNodeVisitors()` which should return a list of node visitors associated to the tag the visit ;

For our node visitor, this could look like this:

```php
namespace App\Sanitizer;

use HtmlSanitizer\Extension\ExtensionInterface;

class MyTagExtension implements ExtensionInterface
{
    public function getName(): string
    {
        return 'my-tag';
    }

    public function createNodeVisitors(array $config = []): array
    {
        return [
            'my-tag' => new MyTagNodeVisitor($config['tags']['my-tag'] ?? []),
            
            // You can also override previous extensions tags here, for instance:
            // 'img' => new MyCustomImgVisitor(),
        ];
    }
}
```

Then, you can use the builder to create a Sanitizer that include this extension:

```php
$builder = new HtmlSanitizer\SanitizerBuilder();
$builder->registerExtension(new HtmlSanitizer\Extension\BasicExtension());
$builder->registerExtension(new HtmlSanitizer\Extension\ListExtension());
// Add the other core ones you need

$builder->registerExtension(new App\Sanitizer\MyTagExtension());

$sanitizer = $builder->build([
    'extensions' => ['basic', 'list', 'my-tag'],
});
```

You can also use `HtmlSanitizer\SanitizerBuilder::createDefault()` to get a `SanitizerBuilder` with all the core extensions:

```php
$builder = HtmlSanitizer\SanitizerBuilder::createDefault();
$builder->registerExtension(new App\Sanitizer\MyTagExtension());

$sanitizer = $builder->build([
    'extensions' => ['basic', 'list', 'my-tag'],
]);
```
