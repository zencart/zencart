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

class HelpCommand extends ConsoleCommand
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
        return 'help';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getDescription(): string
    {
        return 'Display help for a console command.';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUsageLines(): array
    {
        return [
            'php zc_cli.php help <command>',
            'php zc_cli.php <command> --help',
        ];
    }

    /**
     * @since ZC v3.0.0
     */
    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $targetName = $input->getArgument(0);
        if ($targetName === null || $targetName === '') {
            $output->writeln('Usage:');
            foreach ($this->getUsageLines() as $usageLine) {
                $output->writeln('  ' . $usageLine);
            }
            return 0;
        }

        $command = $this->registry->find($targetName);
        if ($command === null) {
            $output->errorln('Command not found: ' . $targetName);
            return 1;
        }

        $output->writeln($command->getName());
        $output->writeln('  ' . $command->getDescription());
        $output->writeln();
        $output->writeln('Usage:');
        foreach ($command->getUsageLines() as $usageLine) {
            $output->writeln('  ' . $usageLine);
        }

        $aliases = $command->getAliases();
        if ($aliases !== []) {
            $output->writeln();
            $output->writeln('Aliases:');
            $output->writeln('  ' . implode(', ', $aliases));
        }

        return 0;
    }
}
