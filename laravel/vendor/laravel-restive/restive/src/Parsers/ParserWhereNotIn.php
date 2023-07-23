<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserWhereNotIn extends ParserWhereAbstract
{
    protected $validator = ['bracketed', ','];

    public function buildQuery(Builder $query) : Builder
    {
        $query = $query->whereNotIn($this->tokens['col'], $this->tokens['in']);
        return $query;
    }
}