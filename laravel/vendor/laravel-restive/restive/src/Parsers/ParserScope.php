<?php
declare(strict_types = 1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserInvalidParameterException;
use Restive\Exceptions\UnknownParserException;

class ParserScope extends ParserAbstract
{
    protected $validator = ['separated', ',', null];

    public function buildQuery(Builder $query) : Builder
    {
        foreach ($this->tokens as $token) {
            try {
                $query = $query->{$token}();
            } catch (\Exception $e) {
                throw new ParserInvalidParameterException('Can\'t find scope: ' . $token);
            }
        }
        return $query;
    }
}