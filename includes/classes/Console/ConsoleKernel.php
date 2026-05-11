<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

use Zencart\Console\Commands\HelpCommand;
use Zencart\Console\Commands\ListCommand;
use Zencart\Console\Commands\ConfigGetCommand;
use Zencart\Console\Commands\PluginListCommand;
use Zencart\Console\Commands\VersionShowCommand;

class ConsoleKernel
{
    private CommandRegistry $registry;
    private CommandResolver $resolver;
    private bool $booted = false;

    /**
     * @since ZC v3.0.0
     *
     * @var string[]
     */
    private array $bootWarnings = [];

    /**
     * @since ZC v3.0.0
     */
    public function __construct(
        ?CommandRegistry $registry = null,
        ?PluginCommandDiscovery $pluginDiscovery = null,
        array $bootWarnings = [],
        private $pluginListProvider = null,
        private $versionProvider = null,
        private $configurationProvider = null
    ) {
        $this->registry = $registry ?? new CommandRegistry();
        $this->resolver = new CommandResolver($this->registry);
        $this->pluginDiscovery = $pluginDiscovery;
        $this->bootWarnings = $bootWarnings;
    }

    /**
     * @since ZC v3.0.0
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->registerCoreCommands();
        $this->registerPluginCommands();
        $this->booted = true;
    }

    /**
     * @since ZC v3.0.0
     */
    public function run(ConsoleInput $input, ConsoleOutput $output): int
    {
        $this->boot();

        foreach ($this->bootWarnings as $warning) {
            $output->errorln('Warning: ' . $warning);
        }

        $command = $this->resolver->resolve($input);
        if ($command === null) {
            $requested = $input->getCommandName() ?? '(none)';
            $output->errorln('Unknown command: ' . $requested);
            $output->errorln('Run `php zc_cli.php list` to see available commands.');
            return 1;
        }

        if ($input->getCommandName() === null && $input->isHelpRequested()) {
            return $this->executeCommand($command, $input, $output);
        }

        if ($input->isHelpRequested() && $command->getName() !== 'help') {
            $helpInput = new ConsoleInput([$input->getScriptName(), 'help', $command->getName()]);
            $helpCommand = $this->registry->find('help');
            return $helpCommand === null ? 1 : $this->executeCommand($helpCommand, $helpInput, $output);
        }

        return $this->executeCommand($command, $input, $output);
    }

    /**
     * @since ZC v3.0.0
     */
    public function getRegistry(): CommandRegistry
    {
        $this->boot();
        return $this->registry;
    }

    private ?PluginCommandDiscovery $pluginDiscovery = null;

    /**
     * @since ZC v3.0.0
     */
    private function registerCoreCommands(): void
    {
        $this->registry->register(new ListCommand($this->registry));
        $this->registry->register(new HelpCommand($this->registry));
        $this->registry->register(new PluginListCommand($this->pluginListProvider));
        $this->registry->register(new VersionShowCommand($this->versionProvider));
        $this->registry->register(new ConfigGetCommand($this->configurationProvider));
    }

    /**
     * @since ZC v3.0.0
     */
    private function registerPluginCommands(): void
    {
        if ($this->pluginDiscovery === null) {
            return;
        }

        foreach ($this->pluginDiscovery->discover() as $command) {
            try {
                $this->registry->register($command);
            } catch (\Throwable $exception) {
                $this->bootWarnings[] = $exception->getMessage();
            }
        }

        $this->bootWarnings = array_merge($this->bootWarnings, $this->pluginDiscovery->getErrors());
    }

    /**
     * @since ZC v3.0.0
     */
    private function executeCommand(ConsoleCommand $command, ConsoleInput $input, ConsoleOutput $output): int
    {
        try {
            return $command->handle($input, $output);
        } catch (\Throwable $exception) {
            $output->errorln('Command failed: ' . $command->getName());

            if ($input->isVerboseRequested()) {
                $output->errorln(get_class($exception) . ': ' . $exception->getMessage());
            } else {
                $output->errorln('Re-run with --verbose for more detail.');
            }

            return 1;
        }
    }
}
