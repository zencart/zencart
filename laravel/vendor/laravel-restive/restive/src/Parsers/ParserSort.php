<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;

class ParserSort extends ParserAbstract
{
    protected $validator = ['separated', ',', null];

    public function tokenize(): void
    {
        parent::tokenize();
        $parts = $this->tokens;
        $this->tokens = [];
        foreach ($parts as $part) {
            $sortDirection = 'ASC';
            if (isset($part[0]) && $part[0] == '-') {
                $sortDirection = 'DESC';
                $part = substr($part, 1);
            }
            $this->tokens[] = ['field' => $part, 'direction' => $sortDirection];
        }
    }

    public function buildQuery(Builder $query) : Builder
    {
        foreach ($this->tokens as $parameters) {
            $query = $query->orderBy($parameters['field'], $parameters['direction']);
        }
        return $query;
    }
}