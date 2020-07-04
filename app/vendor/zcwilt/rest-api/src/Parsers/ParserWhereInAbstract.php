<?php

namespace Zcwilt\Api\Parsers;

use Zcwilt\Api\Exceptions\ParserParameterCountException;

abstract class ParserWhereInAbstract extends ParserAbstract
{
    public function tokenizeParameters(string $parameters)
    {
        $parameters = $this->handleSeparatedParameters($parameters, ':');
        if (count($parameters) !== 2) {
            throw ParserParameterCountException::withCounts('whereIn', 2, count($parameters));
        }
        $this->tokenized['col'] = $parameters[0];
        $this->tokenized['in'] = $this->handleSeparatedParameters(str_replace(['(',')'], '', $parameters[1]));
        if (count($this->tokenized['in']) < 1) {
            throw ParserParameterCountException::withCounts('whereIn in clause ', 1, count($parameters));
        }
    }
}
