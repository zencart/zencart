<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer;

use HtmlSanitizer\Extension\Basic\BasicExtension;
use HtmlSanitizer\Extension\Code\CodeExtension;
use HtmlSanitizer\Extension\Details\DetailsExtension;
use HtmlSanitizer\Extension\ExtensionInterface;
use HtmlSanitizer\Extension\Extra\ExtraExtension;
use HtmlSanitizer\Extension\Iframe\IframeExtension;
use HtmlSanitizer\Extension\Image\ImageExtension;
use HtmlSanitizer\Extension\Listing\ListExtension;
use HtmlSanitizer\Extension\Table\TableExtension;
use HtmlSanitizer\Parser\ParserInterface;
use HtmlSanitizer\Visitor\ScriptNodeVisitor;
use HtmlSanitizer\Visitor\StyleNodeVisitor;
use Psr\Log\LoggerInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @final
 */
class SanitizerBuilder implements SanitizerBuilderInterface
{
    /**
     * @var ExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @var ParserInterface|null
     */
    private $parser;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    public static function createDefault(): self
    {
        $builder = new self();
        $builder->registerExtension(new BasicExtension());
        $builder->registerExtension(new ListExtension());
        $builder->registerExtension(new ImageExtension());
        $builder->registerExtension(new CodeExtension());
        $builder->registerExtension(new TableExtension());
        $builder->registerExtension(new IframeExtension());
        $builder->registerExtension(new DetailsExtension());
        $builder->registerExtension(new ExtraExtension());

        return $builder;
    }

    public function registerExtension(ExtensionInterface $extension)
    {
        $this->extensions[$extension->getName()] = $extension;

        return $this;
    }

    public function setParser(?ParserInterface $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    public function setLogger(?LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function build(array $config): SanitizerInterface
    {
        $nodeVisitors = [];

        foreach ($config['extensions'] ?? [] as $extensionName) {
            if (!isset($this->extensions[$extensionName])) {
                throw new \InvalidArgumentException(sprintf(
                    'You have requested a non-existent sanitizer extension "%s" (available extensions: %s)',
                    $extensionName,
                    implode(', ', array_keys($this->extensions))
                ));
            }

            foreach ($this->extensions[$extensionName]->createNodeVisitors($config) as $tagName => $visitor) {
                $nodeVisitors[$tagName] = $visitor;
            }
        }

        // Always required visitors
        $nodeVisitors['script'] = new ScriptNodeVisitor();
        $nodeVisitors['style'] = new StyleNodeVisitor();

        return new Sanitizer(new DomVisitor($nodeVisitors), $config['max_input_length'] ?? 20000, $this->parser, $this->logger);
    }
}
