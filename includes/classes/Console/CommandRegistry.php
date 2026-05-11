<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

use InvalidArgumentException;

class CommandRegistry
{
    /**
     * @var array<string, ConsoleCommand>
     */
    private array $commands = [];

    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    /**
     * @since ZC v3.0.0
     */
    public function register(ConsoleCommand $command): void
    {
        $name = $command->getName();
        if ($name === '') {
            throw new InvalidArgumentException('Console command names cannot be empty.');
        }

        if (isset($this->commands[$name]) || isset($this->aliases[$name])) {
            throw new InvalidArgumentException('Console command name already registered: ' . $name);
        }

        foreach ($command->getAliases() as $alias) {
            if ($alias === '') {
                throw new InvalidArgumentException('Console command aliases cannot be empty.');
            }

            if (isset($this->commands[$alias]) || isset($this->aliases[$alias])) {
                throw new InvalidArgumentException('Console command alias already registered: ' . $alias);
            }
        }

        $this->commands[$name] = $command;

        foreach ($command->getAliases() as $alias) {
            $this->aliases[$alias] = $name;
        }
    }

    /**
     * @since ZC v3.0.0
     */
    public function find(string $name): ?ConsoleCommand
    {
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }

        if (!isset($this->aliases[$name])) {
            return null;
        }

        return $this->commands[$this->aliases[$name]] ?? null;
    }

    /**
     * @since ZC v3.0.0
     *
     * @return array<string, ConsoleCommand>
     */
    public function all(): array
    {
        ksort($this->commands);
        return $this->commands;
    }
}
