<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserWhereNotBetween extends ParserWhereBetweenAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $eloquentBuilder = $eloquentBuilder->whereNotBetween($this->tokenized[0], [$this->tokenized[1], $this->tokenized[2]]);
        return $eloquentBuilder;
    }
}
