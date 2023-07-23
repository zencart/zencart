<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserWhere extends ParserWhereAbstract
{
    protected $validator = ['separated', ':', 3];

    public function buildQuery(Builder $query) : Builder
    {
        $realOperator = $this->operatorMap[$this->tokens[1]];
        $query = $query->where($this->tokens[0], $realOperator, $this->tokens[2]);
        return $query;
    }
}