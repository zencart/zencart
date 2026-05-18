<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

class ConsoleInput
{
    /**
     * @var string[]
     */
    private array $rawTokens;

    private ?string $commandName = null;

    /**
     * @var string[]
     */
    private array $arguments = [];

    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    /**
     * @since ZC v3.0.0
     *
     * @param string[] $argv
     */
    public function __construct(private array $argv)
    {
        $this->rawTokens = array_values(array_slice($argv, 1));
        $this->parse();
    }

    /**
     * @since ZC v3.0.0
     */
    public function getScriptName(): string
    {
        return $this->argv[0] ?? 'zc_cli.php';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    /**
     * @since ZC v3.0.0
     *
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getArgument(int $index, ?string $default = null): ?string
    {
        return $this->arguments[$index] ?? $default;
    }

    /**
     * @since ZC v3.0.0
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @since ZC v3.0.0
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @since ZC v3.0.0
     *
     * @return string[]
     */
    public function getRawTokens(): array
    {
        return $this->rawTokens;
    }

    /**
     * @since ZC v3.0.0
     */
    public function isHelpRequested(): bool
    {
        return $this->hasOption('help') || $this->hasOption('h');
    }

    /**
     * @since ZC v3.0.0
     */
    public function isVerboseRequested(): bool
    {
        return $this->hasOption('verbose') || $this->hasOption('v');
    }

    /**
     * @since ZC v3.0.0
     */
    private function parse(): void
    {
        $tokens = $this->rawTokens;
        if ($tokens === []) {
            return;
        }

        if ($this->looksLikeOption($tokens[0])) {
            $this->parseTokens($tokens);
            return;
        }

        $this->commandName = array_shift($tokens);
        $this->parseTokens($tokens);
    }

    /**
     * @since ZC v3.0.0
     *
     * @param string[] $tokens
     */
    private function parseTokens(array $tokens): void
    {
        $treatAllAsArguments = false;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if ($treatAllAsArguments) {
                $this->arguments[] = $token;
                continue;
            }

            if ($token === '--') {
                $treatAllAsArguments = true;
                continue;
            }

            if (str_starts_with($token, '--')) {
                $this->parseLongOption($token, $tokens, $i);
                continue;
            }

            if ($token !== '-' && str_starts_with($token, '-')) {
                $this->parseShortOption($token, $tokens, $i);
                continue;
            }

            $this->arguments[] = $token;
        }
    }

    /**
     * @since ZC v3.0.0
     *
     * @param string[] $tokens
     */
    private function parseLongOption(string $token, array $tokens, int &$index): void
    {
        $nameValue = substr($token, 2);
        if ($nameValue === '') {
            return;
        }

        if (str_contains($nameValue, '=')) {
            [$name, $value] = explode('=', $nameValue, 2);
            $this->options[$name] = $value;
            return;
        }

        $nextToken = $tokens[$index + 1] ?? null;
        if ($nextToken !== null && !$this->looksLikeOption($nextToken)) {
            $this->options[$nameValue] = $nextToken;
            $index++;
            return;
        }

        $this->options[$nameValue] = true;
    }

    /**
     * @since ZC v3.0.0
     *
     * @param string[] $tokens
     */
    private function parseShortOption(string $token, array $tokens, int &$index): void
    {
        $flags = substr($token, 1);
        if ($flags === '') {
            return;
        }

        if (strlen($flags) > 1) {
            foreach (str_split($flags) as $flag) {
                $this->options[$flag] = true;
            }
            return;
        }

        $nextToken = $tokens[$index + 1] ?? null;
        if ($nextToken !== null && !$this->looksLikeOption($nextToken)) {
            $this->options[$flags] = $nextToken;
            $index++;
            return;
        }

        $this->options[$flags] = true;
    }

    /**
     * @since ZC v3.0.0
     */
    private function looksLikeOption(string $token): bool
    {
        return $token !== '-' && str_starts_with($token, '-');
    }
}
