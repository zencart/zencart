<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserWhereIn extends ParserWhereAbstract
{
    protected $validator = ['bracketed', ','];

    public function buildQuery(Builder $query) : Builder
    {
        $query = $query->whereIn($this->tokens['col'], $this->tokens['in']);
        return $query;
    }
}