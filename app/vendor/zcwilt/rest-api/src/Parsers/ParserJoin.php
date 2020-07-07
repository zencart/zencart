<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;
use Zcwilt\Api\Exceptions\ParserInvalidParameterException;
use Zcwilt\Api\Exceptions\ParserParameterCountException;

class ParserJoin extends ParserAbstract
{
    /**
     * @var array
     */
    protected $operatorMap = [
        'inner', 'left', 'cross'
    ];

    public function tokenizeParameters(string $parameters)
    {
        $parameters = $this->handleSeparatedParameters($parameters, ':');
        if (count($parameters) !== 4) {
            throw ParserParameterCountException::withCounts('join', 4, count($parameters));
        }
        if (!in_array($parameters[0], $this->operatorMap)) {
            throw new ParserInvalidParameterException("join parser - invalid join type " . $parameters[0]);
        }
        $this->tokenized = $parameters;
    }

    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $tokenized = $this->tokenized;
        $eloquentBuilder = $eloquentBuilder->join($tokenized[1], $tokenized[2], '=', $tokenized[3], $tokenized[0]);
        return $eloquentBuilder;
    }
}
