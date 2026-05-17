<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

abstract class ConsoleCommand
{
    /**
     * @since ZC v3.0.0
     */
    abstract public function getName(): string;

    /**
     * @since ZC v3.0.0
     */
    abstract public function getDescription(): string;

    /**
     * @since ZC v3.0.0
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @since ZC v3.0.0
     *
     * @return string[]
     */
    public function getUsageLines(): array
    {
        return ['php zc_cli.php ' . $this->getName()];
    }

    /**
     * @since ZC v3.0.0
     */
    abstract public function handle(ConsoleInput $input, ConsoleOutput $output): int;
}
