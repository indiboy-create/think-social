<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Tests;

use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Providers\GitHub;
use PHPUnit\Framework\TestCase;
use think\App;
use Youvision\ThinkSocial\Exception\InvalidStateException;
use Youvision\ThinkSocial\Facade\Socialite;
use Youvision\ThinkSocial\Manager;
use Youvision\ThinkSocial\SocialiteService;

class ManagerTest extends TestCase
{
    protected function makeApp(array $config): App
    {
        $app = new App();
        $app->config->set(['socialite' => $config]);

        return $app;
    }

    protected function makeManager(array $config): Manager
    {
        return new Manager($this->makeApp($config));
    }

    public function testDriverUsesDefaultDriverAndBuildsRedirectUrl(): void
    {
        $manager = $this->makeManager([
            'default' => 'github',
            'drivers' => [
                'github' => [
                    'client_id'     => 'test-id',
                    'client_secret' => 'test-secret',
                ],
            ],
        ]);

        $provider = $manager->driver();

        $this->assertInstanceOf(GitHub::class, $provider);

        $url = $provider->withRedirectUrl('https://example.com/callback')->redirect();

        $this->assertStringContainsString('github.com/login/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=test-id', $url);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $url);
    }

    public function testDefaultsAreMergedIntoDriverConfig(): void
    {
        $manager = $this->makeManager([
            'default'  => 'github',
            'defaults' => [
                'guzzle' => ['timeout' => 15],
                'scopes' => ['user:email'],
            ],
            'drivers' => [
                'github' => [
                    'client_id' => 'test-id',
                    'guzzle'    => ['verify' => false],
                ],
            ],
        ]);

        $config = $manager->getDriverConfig('github');

        $this->assertSame(15, $config['guzzle']['timeout']);
        $this->assertFalse($config['guzzle']['verify']);
        $this->assertSame(['user:email'], $config['scopes']);
    }

    public function testCustomProviderCanBeExtended(): void
    {
        $manager = $this->makeManager([
            'default' => 'fake',
            'drivers' => [
                'fake' => ['key' => 'value'],
            ],
        ]);

        $manager->extend('fake', function (array $config) {
            return new FakeProvider($config);
        });

        $provider = $manager->driver('fake');

        $this->assertInstanceOf(FakeProvider::class, $provider);
        $this->assertSame('value', $provider->getConfig()['key']);
    }

    public function testUnknownDriverThrowsException(): void
    {
        $manager = $this->makeManager(['default' => 'unknown']);

        $this->expectException(InvalidArgumentException::class);

        $manager->driver('unknown');
    }

    public function testStateIsGeneratedAndVerified(): void
    {
        $app     = $this->makeApp(['drivers' => []]);
        $session = new FakeSession();

        $app->instance('session', $session);

        $manager = new Manager($app);
        $state   = $manager->generateState('github');

        $this->assertSame($state, $session->get('socialite_state_github'));

        $manager->verifyState('github', $state);

        $this->assertNull($session->get('socialite_state_github'));
    }

    public function testInvalidStateThrowsException(): void
    {
        $app = $this->makeApp(['drivers' => []]);

        $app->instance('session', new FakeSession());

        $manager = new Manager($app);
        $manager->generateState('github');

        $this->expectException(InvalidStateException::class);

        $manager->verifyState('github', 'invalid-state');
    }

    public function testGuzzleOptionsAreAppliedToProvider(): void
    {
        $manager = $this->makeManager([
            'default'  => 'github',
            'defaults' => [
                'guzzle' => [
                    'timeout' => 15,
                    'verify'  => true,
                ],
            ],
            'drivers' => [
                'github' => ['client_id' => 'test-id'],
            ],
        ]);

        $provider = $manager->driver('github');

        $this->assertSame(['timeout' => 15, 'verify' => true], $provider->getGuzzleOptions());
    }

    public function testFacadeResolvesManager(): void
    {
        $app = new App();
        $app->config->set([
            'socialite' => [
                'default' => 'github',
                'drivers' => [
                    'github' => ['client_id' => 'test-id'],
                ],
            ],
        ]);

        $app->register(SocialiteService::class);

        $this->assertInstanceOf(GitHub::class, Socialite::driver());
    }

    public function testServiceRegistersManager(): void
    {
        $app = new App();
        $app->config->set([
            'socialite' => [
                'default' => 'github',
                'drivers' => [
                    'github' => ['client_id' => 'test-id'],
                ],
            ],
        ]);

        $app->register(SocialiteService::class);

        $manager = $app->make('socialite');

        $this->assertInstanceOf(Manager::class, $manager);
        $this->assertSame($manager, $app->make(Manager::class));
    }
}