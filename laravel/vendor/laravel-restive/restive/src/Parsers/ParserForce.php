<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ApiException;

class ParserForce extends ParserAbstract
{
    protected $validator = ['boolean'];

    public function buildQuery(Builder $query) : Builder
    {
        if ($this->tokens[0] !== 'true') {
           return $query;
        }
        $query->forceDelete();
        return $query;
    }
}