<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserJoin extends ParserAbstract
{
    protected $validator = ['separated', ':', 4];

    public function buildQuery(Builder $query) : Builder
    {
        $tokens = $this->tokens;
        $query = $query->join($tokens[1], $tokens[2], '=', $tokens[3], $tokens[0]);
        return $query;
    }
}