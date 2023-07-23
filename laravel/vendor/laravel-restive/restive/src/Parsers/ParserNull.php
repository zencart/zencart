<?php
declare(strict_types = 1);

namespace Restive\Parsers;


use Illuminate\Database\Eloquent\Builder;

class ParserNull extends ParserAbstract
{
    public function tokenize(): void
    {}

    public function buildQuery(Builder $query) : Builder
    {
        return $query;
    }
}