<?php
declare(strict_types=1);

namespace Restive\Parsers;

use Restive\Contracts\Parser;
use Restive\Exceptions\ApiException;
use Restive\Exceptions\ParserInvalidParameterException;
use Restive\Exceptions\ParserParameterCountException;
use Restive\Exceptions\UnknownParserException;

abstract class ParserAbstract implements Parser
{
    protected array $tokens = [];
    protected array $parameters = [];

    public function __construct(string $parserValues)
    {
        $this->parameters['values'] = $parserValues;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function tokenize(): void
    {
        $this->tokens = $this->parseTokens($this->parameters['values'], $this->validator);
        $this->validateTokens($this->tokens, $this->validator);
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    protected function validateTokens(array $tokens, array $validator)
    {
        $type = $validator[0];
        $validatorHandler = 'handleValidator' . ucfirst(strtolower($type));
        if (!method_exists($this, $validatorHandler)) {
            throw new UnknownParserException('Invalid validator handler  - ' . $type);
        }
        $this->{$validatorHandler}($tokens, $validator);
    }

    protected function parseTokens(string $parameters, array $validator)
    {
        $type = $validator[0];
        $tokens = [];
        $parseTokenHandler = 'handle' . ucfirst(strtolower($type)) . 'Tokenizer';
        if (!method_exists($this, $parseTokenHandler)) {
            throw new UnknownParserException('Invalid validator handler  - ' . $type);
            return $tokens;
        }
        $tokens = $this->{$parseTokenHandler}($parameters, $validator);
        return $tokens;
    }

    protected function handleSeparatedTokenizer(string $parameters, array $validator)
    {
        return explode($validator[1], $parameters);
    }

    protected function handleBooleanTokenizer($parameters, $validator)
    {
        return [$parameters];
    }

    protected function handleBracketedTokenizer(string $parameters, array $validator)
    {
        $tokens = [];
        $parts = explode(':', $parameters);
        if (count($parts) !== 2) return $tokens;
        $tokens['col'] = $parts[0];
        $tokens['in'] = explode($validator[1], str_replace(['(', ')'], '', $parts[1]));
        return $tokens;
    }

    protected function handleValidatorSeparated(array $tokens, array $validator)
    {
        if (!is_null($validator[2]) && count($tokens) != $validator[2]) {
            throw (new ParserParameterCountException())->withCounts($validator[0], $validator[2], count($tokens));
        }
        if (is_null($validator[2]) && empty($tokens[0])) {
            throw new ParserInvalidParameterException('Invalid parameter for ' . $validator[0] . ' parser');
        }
    }

    protected function handleValidatorBoolean(array $tokens, array $validator)
    {
        return true;
    }



    protected function handleValidatorBracketed(array $tokens, array $validator): void
    {
        if (!isset($tokens['col'])) {
            throw new ParserInvalidParameterException('invalid options for whereIn clause');
        }
        if (empty($tokens['in'][0])) {
            throw new ParserInvalidParameterException('invalid options for whereIn clause');
        }
    }
}