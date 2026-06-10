<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

class ConsoleOutput
{
    /**
     * @since ZC v3.0.0
     *
     * @param resource|null $stdout
     * @param resource|null $stderr
     */
    public function __construct(
        private $stdout = null,
        private $stderr = null
    ) {
        $this->stdout = $stdout ?? STDOUT;
        $this->stderr = $stderr ?? STDERR;
    }

    /**
     * @since ZC v3.0.0
     */
    public function write(string $message): void
    {
        fwrite($this->stdout, $message);
    }

    /**
     * @since ZC v3.0.0
     */
    public function writeln(string $message = ''): void
    {
        $this->write($message . PHP_EOL);
    }

    /**
     * @since ZC v3.0.0
     */
    public function error(string $message): void
    {
        fwrite($this->stderr, $message);
    }

    /**
     * @since ZC v3.0.0
     */
    public function errorln(string $message): void
    {
        $this->error($message . PHP_EOL);
    }
}
