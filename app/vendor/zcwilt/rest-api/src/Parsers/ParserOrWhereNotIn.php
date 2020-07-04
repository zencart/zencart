<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserOrWhereNotIn extends ParserWhereInAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $eloquentBuilder = $eloquentBuilder->orWhereNotIn($this->tokenized['col'], $this->tokenized['in']);
        return $eloquentBuilder;
    }
}
