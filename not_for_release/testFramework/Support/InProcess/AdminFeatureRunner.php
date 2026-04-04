<?php

namespace Tests\Support\InProcess;

class AdminFeatureRunner extends InProcessFeatureRunner
{
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
        $requestFile = tempnam(sys_get_temp_dir(), 'zc-inprocess-admin-request-');
        $responseFile = tempnam(sys_get_temp_dir(), 'zc-inprocess-admin-response-');

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
        ];

        file_put_contents($requestFile, json_encode($payload));

        try {
            $this->executeChildRequest($requestFile, $responseFile);
            $responsePayload = json_decode((string) file_get_contents($responseFile), true);

            if (!is_array($responsePayload)) {
                throw new InProcessFeatureException('AdminFeatureRunner child request did not return a readable response payload.');
            }

            if (is_array($responsePayload['last_error'] ?? null)) {
                $error = $responsePayload['last_error'];
                throw new InProcessFeatureException(sprintf(
                    'Admin request failed: %s in %s:%s',
                    $error['message'] ?? 'Unknown error',
                    $error['file'] ?? 'unknown file',
                    $error['line'] ?? '0'
                ));
            }

            return new FeatureResponse(
                (int) ($responsePayload['status_code'] ?? 200),
                (string) ($responsePayload['content'] ?? ''),
                ($responsePayload['headers'] ?? []) + ['X-ZC-InProcess-Runner' => 'admin'],
                $responsePayload['cookies'] ?? []
            );
        } finally {
            @unlink($requestFile);
            @unlink($responseFile);
        }
    }

    private function executeChildRequest(string $requestFile, string $responseFile): void
    {
        $script = __DIR__ . '/execute_admin_request.php';
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
                "Admin child request failed.\n" . implode(PHP_EOL, $output)
            );
        }
    }

    private function resolveEntrypoint(FeatureRequest $request): string
    {
        $path = $request->requestPath();

        if ($this->entrypoint !== null) {
            return $this->entrypoint;
        }

        if (in_array($path, ['/admin', '/admin/', '/admin/index.php'], true)) {
            return $this->resolvedDocumentRoot() . '/admin/index.php';
        }

        if (preg_match('#^/admin/[A-Za-z0-9_.-]+\.php$#', $path) === 1) {
            $entrypoint = $this->resolvedDocumentRoot() . $path;
            if (is_file($entrypoint)) {
                return $entrypoint;
            }
        }

        throw new InProcessFeatureException(sprintf('Unsupported admin path for in-process dispatch: %s', $path));
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
