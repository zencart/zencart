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
 * Returns the value of `require` when invoked (thereby requiring the file).
 *
 * @package Aura.Di
 *
 */
class LazyRequire implements LazyInterface
{
    /**
     *
     * The file to require.
     *
     * @var string|LazyInterface
     *
     */
    protected $file;

    /**
     *
     * Constructor.
     *
     * @param string|LazyInterface $file The file to require.
     *
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     *
     * Invokes the closure to require the file.
     *
     * @return mixed The return from the required file, if any.
     *
     */
    public function __invoke()
    {
        $filename = $this->file;
        if ($filename instanceof LazyInterface) {
            $filename = $filename->__invoke();
        }
        return require $filename;
    }
}
