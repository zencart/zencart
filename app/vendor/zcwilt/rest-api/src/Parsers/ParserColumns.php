<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;
use Zcwilt\Api\Exceptions\ParserParameterCountException;

class ParserColumns extends ParserAbstract
{
    public function tokenizeParameters(string $parameters)
    {
        $parameters = $this->handleSeparatedParameters($parameters);
        if (count($parameters) === 0) {
            throw new ParserParameterCountException("columns parser - missing parameters");
        }
        foreach ($parameters as $field) {
            $this->tokenized[] = ['field' => $field];
        }
    }

    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        foreach ($this->tokenized as $parameters) {
            $field = $parameters['field'];
            $eloquentBuilder = $eloquentBuilder->addSelect($field);
        }
        return $eloquentBuilder;
    }
}
