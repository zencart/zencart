<?php

namespace Tests\Support\InProcess;

class FeatureRequest
{
    public function __construct(
        public readonly string $uri,
        public readonly string $method = 'GET',
        public readonly array $query = [],
        public readonly array $request = [],
        public readonly array $server = [],
        public readonly array $cookies = [],
    ) {
    }

    public function withServer(array $server): self
    {
        return new self(
            $this->uri,
            $this->method,
            $this->query,
            $this->request,
            array_merge($this->server, $server),
            $this->cookies
        );
    }

    public function requestPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);

        return $path ?: '/';
    }

    public function queryParameters(): array
    {
        $query = $this->query;
        $uriQuery = parse_url($this->uri, PHP_URL_QUERY);
        if (!empty($uriQuery)) {
            parse_str($uriQuery, $uriValues);
            $query = array_merge($uriValues, $query);
        }

        return $query;
    }
}
