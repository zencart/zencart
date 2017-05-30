<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

/**
 *
 * Returns the value of `include` when invoked (thereby including the file).
 *
 * @package Aura.Di
 *
 */
class LazyInclude implements LazyInterface
{
    /**
     *
     * The file to include.
     *
     * @var string|LazyInterface
     *
     */
    protected $file;

    /**
     *
     * Constructor.
     *
     * @param string|LazyInterface $file The file to include.
     *
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     *
     * Invokes the closure to include the file.
     *
     * @return mixed The return from the included file, if any.
     *
     */
    public function __invoke()
    {
        $filename = $this->file;
        if ($filename instanceof LazyInterface) {
            $filename = $filename->__invoke();
        }
        return include $filename;
    }
}
