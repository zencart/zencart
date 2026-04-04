<?php

namespace Tests\Support\InProcess;

class StorefrontFeatureRunner extends InProcessFeatureRunner
{
    private static ?string $shutdownDebugPath = null;

    public function __construct(
        ?ApplicationStateResetter $stateResetter = null,
        private readonly ?string $entrypoint = null,
        private readonly ?string $documentRoot = null,
    ) {
        parent::__construct($stateResetter);
    }

    protected function dispatch(FeatureRequest $request): FeatureResponse
    {
        $entrypoint = $this->resolveEntrypoint($request);
        $requestFile = tempnam(sys_get_temp_dir(), 'zc-inprocess-request-');
        $responseFile = tempnam(sys_get_temp_dir(), 'zc-inprocess-response-');

        $payload = [
            'uri' => $request->uri,
            'method' => $request->method,
            'query' => $request->query,
            'request' => $request->request,
            'server' => $request->server,
            'cookies' => $request->cookies,
            'entrypoint' => $entrypoint,
            'document_root' => $this->resolvedDocumentRoot(),
            'root_cwd' => $this->resolvedRootCwd(),
            'debug_path' => self::$shutdownDebugPath,
        ];

        file_put_contents($requestFile, json_encode($payload));

        try {
            $this->executeChildRequest($requestFile, $responseFile);
            $responsePayload = json_decode((string) file_get_contents($responseFile), true);

            if (!is_array($responsePayload)) {
                throw new InProcessFeatureException('StorefrontFeatureRunner child request did not return a readable response payload.');
            }

            if (is_array($responsePayload['last_error'] ?? null)) {
                $error = $responsePayload['last_error'];
                throw new InProcessFeatureException(sprintf(
                    'Storefront request failed: %s in %s:%s',
                    $error['message'] ?? 'Unknown error',
                    $error['file'] ?? 'unknown file',
                    $error['line'] ?? '0'
                ));
            }

            return new FeatureResponse(
                (int) ($responsePayload['status_code'] ?? 200),
                (string) ($responsePayload['content'] ?? ''),
                ($responsePayload['headers'] ?? []) + ['X-ZC-InProcess-Runner' => 'storefront'],
                $responsePayload['cookies'] ?? []
            );
        } finally {
            @unlink($requestFile);
            @unlink($responseFile);
            self::$shutdownDebugPath = null;
        }
    }

    public static function resetDispatchState(): void
    {
        self::$shutdownDebugPath = null;
    }

    public static function writeShutdownDebugTo(string $path): void
    {
        self::$shutdownDebugPath = $path;
    }

    private function executeChildRequest(string $requestFile, string $responseFile): void
    {
        $script = __DIR__ . '/execute_storefront_request.php';
        $phpBinary = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $command = escapeshellarg($phpBinary)
            . ' ' . escapeshellarg($script)
            . ' ' . escapeshellarg($requestFile)
            . ' ' . escapeshellarg($responseFile)
            . ' 2>&1';

        $output = [];
        $status = 0;
        exec($command, $output, $status);

        if ($status !== 0 && !file_exists($responseFile)) {
            throw new InProcessFeatureException(
                "Storefront child request failed.\n" . implode(PHP_EOL, $output)
            );
        }
    }

    private function resolveEntrypoint(FeatureRequest $request): string
    {
        $path = $request->requestPath();

        if ($this->entrypoint !== null) {
            return $this->entrypoint;
        }

        if ($path !== '/' && $path !== '/index.php') {
            throw new InProcessFeatureException(sprintf('Unsupported storefront path for in-process dispatch: %s', $path));
        }

        return $this->resolvedDocumentRoot() . '/index.php';
    }

    private function resolvedDocumentRoot(): string
    {
        $documentRoot = $this->documentRoot;
        if ($documentRoot === null) {
            $documentRoot = defined('ROOTCWD') ? rtrim(ROOTCWD, '/') : getcwd();
        }

        return rtrim($documentRoot, '/');
    }

    private function resolvedRootCwd(): string
    {
        if (defined('ROOTCWD')) {
            return rtrim(ROOTCWD, '/') . '/';
        }

        return realpath(__DIR__ . '/../../../..') . '/';
    }
}
