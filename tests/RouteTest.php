<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Tests;

use PHPUnit\Framework\TestCase;
use think\App;
use think\Request;

class RouteTest extends TestCase
{
    protected array $server;
    protected array $get;

    protected function setUp(): void
    {
        $this->server = $_SERVER;
        $this->get    = $_GET;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME']    = '/index.php';
        $_SERVER['HTTP_HOST']      = 'example.com';
        $_SERVER['SERVER_NAME']    = 'example.com';
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        $_GET    = $this->get;
    }

    protected function runRequest(string $path, array $query = [])
    {
        $_SERVER['PATH_INFO']   = $path;
        $_SERVER['REQUEST_URI'] = $path . ($query ? '?' . http_build_query($query) : '');
        $_GET                   = $query;

        $app = new App(__DIR__ . '/fixtures/webapp');

        return $app->http->run($app->make('request', [], true));
    }

    public function testBuiltInRedirectRouteReturnsRedirect(): void
    {
        $response = $this->runRequest('/socialite/github');

        $this->assertSame(302, $response->getCode());
        $this->assertStringContainsString(
            'github.com/login/oauth/authorize',
            (string) $response->getHeader('Location')
        );
        $this->assertStringContainsString(
            'client_id=test-id',
            (string) $response->getHeader('Location')
        );
        $this->assertStringContainsString(
            'redirect_uri=http%3A%2F%2Fexample.com%2Fsocialite%2Fgithub%2Fcallback',
            (string) $response->getHeader('Location')
        );
    }

    public function testCallbackReturnsJsonErrorWhenProviderReturnsError(): void
    {
        $response = $this->runRequest('/socialite/github/callback', [
            'error'             => 'access_denied',
            'error_description' => 'The user denied the request',
        ]);

        $this->assertSame(400, $response->getCode());

        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame('access_denied', $data['error']);
    }

    public function testCallbackWithoutCodeReturnsJsonError(): void
    {
        $response = $this->runRequest('/socialite/github/callback');

        $this->assertSame(400, $response->getCode());

        $data = json_decode((string) $response->getContent(), true);

        $this->assertSame('authorization_failed', $data['error']);
    }
}