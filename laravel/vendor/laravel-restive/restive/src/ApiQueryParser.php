<?php declare(strict_types=1);

namespace Restive;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\http\Request;
use Restive\Exceptions\ApiException;

class ApiQueryParser
{
    protected ParserFactory $parserFactory;

    protected array $errors = [];

    public function __construct(ParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
    }

    public function buildParseKeys(Request $request): array
    {
        $queryString = rawurldecode($request->server()['QUERY_STRING'] ?? '');
        return $this->parseQueryString($queryString);
    }

    public function buildParserList(array $parsedQuery): array
    {
        $parserList = [];
        foreach ($parsedQuery as $query) {
            $parserList[] = $this->parserFactory->getParser($query[0], $query[1]);
        }
        return $parserList;
    }

    public function executeParsers(array $parsers, $model): Builder
    {
        $query = $model->query();
        foreach ($parsers as $parser) {
            try {
                $parser->tokenize();
            } catch (ApiException $e) {
                $this->addError($e->getMessage(), $parser);
                continue;
            }
            try {
                $query = $parser->buildQuery($query);
            } catch (ApiException $e) {
                $this->addError($e->getMessage(), $parser);
                continue;
            }
        }
        if ($this->hasErrors()) {
            throw new ApiException(implode(', ', $this->getErrors()));
        }
        return $query;
    }

    protected function parseQueryString(string $queryString): array
    {
        $whitelist = ['page', 'per_page', 'limit'];
        $whitelist = array_merge($whitelist, config('restive.whitelist', []));
        $queryParameters = [];
        if (trim($queryString) === '') {
            return $queryParameters;
        }
        $queryParts = explode('&', trim($queryString));
        foreach ($queryParts as $queryPart) {
            $parts = explode('=', $queryPart);
            $queryKey = $parts[0] ?? '';
            if (in_array($queryKey, $whitelist)) {
                continue;
            }
            $queryKey = rtrim($queryKey, '[]');
            $queryValue = $parts[1] ?? '';
            $queryParameters[] = [$queryKey, $queryValue];
        }
        return $queryParameters;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return (bool)count($this->errors);
    }

    protected function addError(string $error)
    {
        $this->errors[] = $error;
    }
}