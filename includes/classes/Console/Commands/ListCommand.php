<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console\Commands;

use Zencart\Console\CommandRegistry;
use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class ListCommand extends ConsoleCommand
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
    public function getName(): string
    {
        return 'list';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getDescription(): string
    {
        return 'List available console commands.';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getAliases(): array
    {
        return ['ls'];
    }

    /**
     * @since ZC v3.0.0
     */
    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $output->writeln('Available commands:');

        foreach ($this->registry->all() as $command) {
            $output->writeln(sprintf('  %-20s %s', $command->getName(), $command->getDescription()));
        }

        return 0;
    }
}
