<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use Tests\Support\InProcess\AdminFeatureRunner;
use Tests\Support\InProcess\FeatureRequest;
use Tests\Support\InProcess\FeatureResponse;

abstract class zcInProcessFeatureTestCaseAdmin extends zcInProcessFeatureTestCase
{
    protected array $cookies = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::setUpInProcessFeatureContext('admin');
    }

    public function setUp(): void
    {
        $this->cookies = [];
    }

    protected function getAdmin(string $uri = '/admin', array $server = [], array $cookies = []): FeatureResponse
    {
        $response = (new AdminFeatureRunner())->handle(
            new FeatureRequest($uri, 'GET', server: $server, cookies: $this->mergeCookies($cookies))
        );

        $this->storeResponseCookies($response);

        return $response;
    }

    protected function postAdmin(string $uri, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $response = (new AdminFeatureRunner())->handle(
            new FeatureRequest($uri, 'POST', request: $data, server: $server, cookies: $this->mergeCookies($cookies))
        );

        $this->storeResponseCookies($response);

        return $response;
    }

    protected function visitAdminHome(array $server = [], array $cookies = []): FeatureResponse
    {
        $response = $this->getAdmin('/admin', $server, $cookies);

        return $response->isRedirect() ? $this->followAdminRedirect($response, $server) : $response;
    }

    protected function visitAdminCommand(string $command, array $server = [], array $cookies = []): FeatureResponse
    {
        return $this->getAdmin('/admin/index.php?cmd=' . $command, $server, $cookies);
    }

    protected function submitAdminLogin(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitAdminHome($server, $cookies)->assertOk();
        $formAction = $page->formAction('loginForm') ?? '/admin/index.php';

        $response = $this->postAdmin(
            $this->normalizeAdminUri($formAction),
            array_merge($page->formDefaults('loginForm'), $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followAdminRedirect($response, $server) : $response;
    }

    protected function submitAdminSetupWizard(array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $page = $this->visitAdminHome($server, $cookies)->assertOk();
        $formAction = $page->formAction('setupWizardForm') ?? '/admin/index.php';

        $response = $this->postAdmin(
            $this->normalizeAdminUri($formAction),
            array_merge($page->formDefaults('setupWizardForm'), $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followAdminRedirect($response, $server) : $response;
    }

    protected function submitAdminForm(FeatureResponse $page, string $formId, array $data = [], array $server = [], array $cookies = []): FeatureResponse
    {
        $formAction = $page->formAction($formId) ?? '/admin/index.php';

        $response = $this->postAdmin(
            $this->normalizeAdminUri($formAction),
            array_merge($page->formDefaults($formId), $data),
            $server,
            $cookies
        );

        return $response->isRedirect() ? $this->followAdminRedirect($response, $server) : $response;
    }

    protected function followAdminRedirect(FeatureResponse $response, array $server = []): FeatureResponse
    {
        $response->assertRedirect();
        $location = $response->redirectLocation();
        $this->assertNotNull($location);

        $parts = parse_url($location);
        $path = $parts['path'] ?? '/admin';
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $this->getAdmin($path, array_merge($server, $this->serverFromLocation($location)));
    }

    private function mergeCookies(array $cookies): array
    {
        return array_merge($this->cookies, $cookies);
    }

    private function storeResponseCookies(FeatureResponse $response): void
    {
        $this->cookies = array_merge($this->cookies, $response->cookies);
    }

    private function normalizeAdminUri(string $uri): string
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            return $uri;
        }

        $path = $parts['path'] ?? '/admin/index.php';
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }

    private function serverFromLocation(string $location): array
    {
        $parts = parse_url($location);
        if (!is_array($parts)) {
            return [];
        }

        $server = [];
        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme === 'https') {
            $server['HTTPS'] = 'on';
            $server['SERVER_PORT'] = '443';
        } elseif ($scheme === 'http') {
            $server['HTTPS'] = 'off';
            $server['SERVER_PORT'] = '80';
        }

        if (!empty($parts['host'])) {
            $server['HTTP_HOST'] = $parts['host'];
            $server['SERVER_NAME'] = $parts['host'];
        }

        return $server;
    }
}
