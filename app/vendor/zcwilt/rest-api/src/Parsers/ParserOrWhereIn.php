<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserOrWhereIn extends ParserWhereInAbstract
{
    public function prepareQuery(Builder $eloquentBuilder): Builder
    {
        $eloquentBuilder = $eloquentBuilder->orWhereIn($this->tokenized['col'], $this->tokenized['in']);
        return $eloquentBuilder;
    }
}
