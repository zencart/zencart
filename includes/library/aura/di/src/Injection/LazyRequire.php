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
     * @var string
     *
     */
    protected $file;

    /**
     *
     * Constructor.
     *
     * @param string $file The file to require.
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
        return require $this->file;
    }
}
