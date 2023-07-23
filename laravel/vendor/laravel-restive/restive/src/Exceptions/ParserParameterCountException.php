<?php

namespace Restive\Exceptions;

class ParserParameterCountException extends ApiException
{
    public static function withCounts(string $parser, int $expects, int $actual)
    {
        $message = sprintf("%s parser expects %s parameters, found %s parameters", $parser, $expects, $actual);
        return new static($message);
    }
}
