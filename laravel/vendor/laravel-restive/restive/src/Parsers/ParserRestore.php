<?php

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ApiException;

class ParserRestore extends ParserAbstract
{
    protected $validator = ['boolean'];

    public function buildQuery(Builder $query): Builder
    {
        if ($this->tokens[0] !== 'true') {
            return $query;
        }
        try {
            $query->restore();
        } catch (\BadMethodCallException $e) {
            throw new ApiException('Model does not support soft deletes');
        }
        return $query;
    }
}
