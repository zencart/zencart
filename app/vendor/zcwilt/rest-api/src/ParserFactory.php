<?php

namespace Zcwilt\Api;

use Zcwilt\Api\Exceptions\UnknownParserException;
use Zcwilt\Api\Parsers\ParserInterface;

class ParserFactory
{
    public function getParser(string $method): ParserInterface
    {
        $classname = __NAMESPACE__ . '\\Parsers\\' . 'Parser' . ucfirst($method);
        if (!class_exists($classname)) {
            throw new UnknownParserException("Can't find parser class " . $classname);
        }
        $class = new $classname();
        return $class;
    }
}
