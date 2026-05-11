<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Plugins\Console\ZenTestPlugin\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class DemoCommand extends ConsoleCommand
{
    public function getName(): string
    {
        return 'zen-test:demo';
    }

    public function getDescription(): string
    {
        return 'Demo plugin console command used by the unit tests.';
    }

    public function getUsageLines(): array
    {
        return ['php zc_cli.php zen-test:demo [name]'];
    }

    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $output->writeln('Hello ' . ($input->getArgument(0, 'world')));
        return 0;
    }
}
