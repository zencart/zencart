<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserOrWhereNotBetween extends ParserWhereBetweenAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $eloquentBuilder = $eloquentBuilder->orWhereNotBetween($this->tokenized[0], [$this->tokenized[1], $this->tokenized[2]]);
        return $eloquentBuilder;
    }
}
