<?php

namespace Zcwilt\Api\Parsers;

use Zcwilt\Api\Exceptions\ParserParameterCountException;
use Illuminate\Database\Eloquent\Builder;

class ParserSort extends ParserAbstract
{
    public function tokenizeParameters(string $parameters)
    {
        $parameters = $this->handleSeparatedParameters($parameters);
        if (count($parameters) === 0) {
            throw new ParserParameterCountException("sort parser - missing parameters");
        }
        foreach ($parameters as $field) {
            $sortDirection = 'ASC';
            if (isset($field[0]) && $field[0] == '-') {
                $sortDirection = 'DESC';
                $field = substr($field, 1);
            }
            $this->tokenized[] = ['field' => $field, 'direction' => $sortDirection];
        }
    }

    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        foreach ($this->tokenized as $parameters) {
            $eloquentBuilder = $eloquentBuilder->orderBy($parameters['field'], $parameters['direction']);
        }
        return $eloquentBuilder;
    }
}
