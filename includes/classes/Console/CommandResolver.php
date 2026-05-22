<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

class CommandResolver
{
    /**
     * @since ZC v3.0.0
     */
    public function __construct(private CommandRegistry $registry)
    {
    }

    /**
     * @since ZC v3.0.0
     */
    public function resolve(ConsoleInput $input): ?ConsoleCommand
    {
        $commandName = $input->getCommandName();

        if ($commandName === null || $commandName === '') {
            return $this->registry->find('list');
        }

        return $this->registry->find($commandName);
    }
}
