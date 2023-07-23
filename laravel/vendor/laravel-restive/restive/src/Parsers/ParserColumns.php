<?php
declare(strict_types=1);

namespace Restive\Parsers;

use Illuminate\Database\Eloquent\Builder;
use Restive\Exceptions\ParserInvalidParameterException;

class ParserColumns extends ParserAbstract
{
    protected $validator = ['separated', ',', null];

    public function buildQuery(Builder $query): Builder
    {
        foreach ($this->tokens as $token) {
            try {
                $query = $query->addSelect($token);
            } catch (\Exception $e) {
                throw new ParserInvalidParameterException('Can\'t find column: ' . $token);
            }
        }

        return $query;
    }
}
