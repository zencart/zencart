<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserOrWhere extends ParserWhereAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $tokenized = $this->tokenized;
        $realOperator = $this->operatorMap[$tokenized[1]];
        $eloquentBuilder = $eloquentBuilder->orWhere($tokenized[0], $realOperator, $tokenized[2]);
        return $eloquentBuilder;
    }
}
