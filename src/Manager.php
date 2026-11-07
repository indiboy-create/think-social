<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial;

use Overtrue\Socialite\Config as SocialiteConfig;
use Overtrue\Socialite\Contracts\ProviderInterface;
use Overtrue\Socialite\Contracts\UserInterface;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Providers\Base;
use Overtrue\Socialite\SocialiteManager;
use think\App;
use Youvision\ThinkSocial\Exception\InvalidStateException;

/**
 * 社交登录管理器。
 *
 * 在 overtrue/socialite 的基础上增加了 ThinkPHP 的配置读取、
 * state 校验、回调地址生成和默认配置合并能力。
 */
class Manager extends SocialiteManager
{
    protected App $app;

    /**
     * socialite.php 中的完整配置。
     */
    protected array $settings = [];

    public function __construct(App $app)
    {
        $this->app     = $app;
        $settings      = (array) $app->config->get('socialite', []);
        $this->settings = $settings;

        parent::__construct($this->prepareDrivers($settings));
    }

    /**
     * 获取指定驱动，未传时使用配置中的默认驱动。
     */
    public function driver(?string $driver = null): ProviderInterface
    {
        return $this->create($driver ?: $this->getDefaultDriver());
    }

    /**
     * 默认驱动名称。
     */
    public function getDefaultDriver(): string
    {
        return (string) ($this->settings['default'] ?? 'wechat');
    }

    /**
     * 是否关闭 state 校验。
     */
    public function isStateless(): bool
    {
        return (bool) ($this->settings['stateless'] ?? false);
    }

    /**
     * 是否为每个驱动生成并校验 state。
     */
    public function isStateful(): bool
    {
        return ! $this->isStateless();
    }

    /**
     * 生成跳转到第三方平台的地址。
     */
    public function redirect(?string $driver = null, ?string $redirectUrl = null): string
    {
        $driver   = $driver ?: $this->getDefaultDriver();
        $provider = $this->driver($driver);

        if (! empty($redirectUrl)) {
            $provider->withRedirectUrl($redirectUrl);
        }

        if ($this->isStateful()) {
            $provider->withState($this->generateState($driver));
        }

        return $provider->redirect();
    }

    /**
     * 使用回调 code 获取第三方用户信息。
     *
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function user(?string $driver = null, ?string $code = null, ?string $state = null): UserInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();
        $code   = $code ?? (string) $this->app->request->get('code', '');
        $state  = $state ?? (string) $this->app->request->get('state', '');

        if ($code === '') {
            throw new InvalidArgumentException('The authorization code is empty.');
        }

        if ($this->isStateful()) {
            $this->verifyState($driver, $state);
        }

        return $this->driver($driver)->userFromCode($code);
    }

    /**
     * 生成 state 并写入 Session。
     */
    public function generateState(string $driver): string
    {
        $state = bin2hex(random_bytes(20));

        $this->app->session->set($this->stateKey($driver), $state);

        return $state;
    }

    /**
     * 校验回调返回的 state，并删除 Session 中的旧值。
     *
     * @throws InvalidStateException
     */
    public function verifyState(string $driver, string $state): void
    {
        $key      = $this->stateKey($driver);
        $expected = $this->app->session->get($key);

        $this->app->session->delete($key);

        if (! is_string($expected) || $expected === '' || ! hash_equals($expected, $state)) {
            throw new InvalidStateException('Invalid OAuth state.');
        }
    }

    /**
     * 生成当前驱动的回调地址。
     */
    public function callbackUrl(string $driver): string
    {
        $configured = (string) $this->app->config->get('socialite.routes.callback', '');

        if ($configured !== '') {
            return $configured;
        }

        $prefix = trim((string) $this->app->config->get('socialite.routes.prefix', 'socialite'), '/');
        $path   = ($prefix !== '' ? '/' . $prefix : '') . '/' . $driver . '/callback';

        return rtrim($this->app->request->domain(true), '/') . $path;
    }

    /**
     * 获取驱动合并默认值后的配置。
     */
    public function getDriverConfig(string $driver): array
    {
        return (array) $this->config->get(strtolower($driver), []);
    }

    /**
     * 创建驱动实例，并应用 guzzle 配置。
     */
    protected function createProvider(string $name): ProviderInterface
    {
        $provider = parent::createProvider($name);
        $config   = (array) $this->config->get(strtolower($name), []);

        if ($provider instanceof Base && ! empty($config['guzzle']) && is_array($config['guzzle'])) {
            $provider->setGuzzleOptions($config['guzzle']);
        }

        return $provider;
    }

    /**
     * 将 defaults 合并进每个驱动配置。
     */
    protected function prepareDrivers(array $settings): array
    {
        $defaults = (array) ($settings['defaults'] ?? []);
        $drivers  = (array) ($settings['drivers'] ?? []);
        $prepared = [];

        foreach ($drivers as $name => $config) {
            $prepared[$name] = array_replace_recursive($defaults, (array) $config);
        }

        return $prepared;
    }

    protected function stateKey(string $driver): string
    {
        return 'socialite_state_' . strtolower($driver);
    }
}