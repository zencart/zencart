<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserWhereIn extends ParserWhereInAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $eloquentBuilder = $eloquentBuilder->whereIn($this->tokenized['col'], $this->tokenized['in']);
        return $eloquentBuilder;
    }
}
