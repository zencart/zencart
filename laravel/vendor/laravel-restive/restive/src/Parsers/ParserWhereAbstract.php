<?php
declare(strict_types = 1);

namespace Restive\Parsers;

abstract class ParserWhereAbstract extends ParserAbstract
{
    protected $operatorMap = [
        'eq' => '=',
        'noteq' => '!=',
        'lte' => '<=',
        'gte' => '>=',
        'gt' => '>',
        'lt' => '<',
        'lk' => 'LIKE',
        'nlk' => 'NOT LIKE',
    ];
}
