<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserOrWhereBetween extends ParserWhereAbstract
{
    protected $validator = ['separated', ':', 3];

    public function buildQuery(Builder $query) : Builder
    {
        $query = $query->orWhereBetween($this->tokens[0], [$this->tokens[1], $this->tokens[2]]);
        return $query;
    }
}