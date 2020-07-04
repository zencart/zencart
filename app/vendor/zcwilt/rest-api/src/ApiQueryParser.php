<?php

namespace Zcwilt\Api;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ApiQueryParser
{
    /*
     * @var ParserFactory
     *
     */
    protected $parserFactory;

    /*
     * @var array
     *
     */
    protected $queryParts;

    /*
     * @var array
     *
     */
    protected $parsedKeys;

    public function __construct(ParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
        $this->queryParts = [];
    }

    public function parseRequest(Request $request): ApiQueryParser
    {
        $this->parsedKeys = [];
        if (count($this->getQueryParams($request))) {
            $this->gatherKeys($request);
        }
        return $this;
    }

    public function buildParsers(): ApiQueryParser
    {
        $this->parseKeys();
        return $this;
    }

    public function buildQuery(Model $model): Builder
    {
        if (!isset($this->parsedKeys['columns'])) {
            $this->parsedKeys['columns'] = $model->getTable() . '.*';
        }
        $query = $model->query();
        $query = $this->buildRawQuery($query);
        return $query;
    }

    public function getQueryParts(): array
    {
        return $this->queryParts;
    }

    public function getParserFactory(): ParserFactory
    {
        return $this->parserFactory;
    }

    public function getParsedKeys(): array
    {
        return $this->parsedKeys;
    }

    protected function gatherKeys(Request $request)
    {
        $ignoreKeys = ['page', 'per_page', 'paginate', 'fields'];
        foreach ($this->getQueryParams($request) as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }
            $this->parsedKeys[$key] = $value;
        }
    }

    protected function parseKeys()
    {
        foreach ($this->parsedKeys as $action => $parameters) {
            if (!is_array($parameters)) {
                $parameters = [$parameters];
            }
            $this->parseKeysFromArray($action, $parameters);
        }
    }

    protected function parseKeysFromArray(string $action, array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->callParser($action, (string)$parameter);
        }
    }

    protected function callParser(string $action, string $parameters)
    {
        $parser = $this->parserFactory->getParser($action);
        $parser->parse($parameters);
        $this->queryParts[] = $parser;
    }

    protected function buildRawQuery(Builder $eloquentQB): Builder
    {
        foreach ($this->queryParts as $parser) {
            $eloquentQB = $parser->addQuery($eloquentQB);
        }
        return $eloquentQB;
    }

    protected function getQueryParams(Request $request): array
    {
        $params = $request->query();
        $params = array_merge($params, $request->all());
        return $params;
    }
}
