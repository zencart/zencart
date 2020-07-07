<?php

namespace Zcwilt\Api\Parsers;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Zcwilt\Api\Exceptions\ParserInvalidParameterException;

abstract class ParserWhereAbstract extends ParserAbstract
{
    /**
     * @var array
     */
    protected $operatorMap = [
        'eq' => '=',
        'noteq' => '!=',
        'lte' => '<=',
        'gte' => '>=',
        'gt' => '>',
        'lt' => '<',
        'lk' => 'LIKE',
        'nlk' => 'NOT LIKE',
    ];

    public function tokenizeParameters(string $parameters)
    {
        $parameters = $this->handleSeparatedParameters($parameters, ':');
        if (count($parameters) !== 3) {
            throw ParserParameterCountException::withCounts('where', 3, count($parameters));
        }
        if (!array_key_exists($parameters[1], $this->operatorMap)) {
            throw new ParserInvalidParameterException("where parser - invalid parameters");
        }
        $this->tokenized = $parameters;
    }
}
