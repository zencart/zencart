<?php

namespace Zcwilt\Api\Parsers;

use Illuminate\Database\Eloquent\Builder;

abstract class ParserAbstract implements ParserInterface
{
    /**
     * @var array
     */
    protected $tokenized;

    public function __construct()
    {
        $this->tokenized = array();
    }

    public function parse(string $parameters)
    {
        $this->tokenizeParameters($parameters);
    }

    public function addQuery(Builder $eloquentBuilder): Builder
    {
        return $this->prepareQuery($eloquentBuilder);
    }

    public function getTokenized(): array
    {
        return $this->tokenized;
    }

    public function handleSeparatedParameters(string $parameters, string $separator = ','): array
    {
        $result = [];
        if (trim($parameters) === '') {
            return [];
        }
        $parameters = explode($separator, $parameters);

        foreach ($parameters as $parameter) {
            if (trim($parameter)) {
                $result[] = trim($parameter);
            }
        }
        return ($result);
    }
}
