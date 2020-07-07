<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserWhere extends ParserWhereAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $tokenized = $this->tokenized;
        $realOperator = $this->operatorMap[$tokenized[1]];
        $eloquentBuilder = $eloquentBuilder->where($tokenized[0], $realOperator, $tokenized[2]);
        return $eloquentBuilder;
    }
}
