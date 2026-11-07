<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Tests;

use Overtrue\Socialite\Contracts\ProviderInterface;
use Overtrue\Socialite\Contracts\UserInterface;

class FakeProvider implements ProviderInterface
{
    public function __construct(protected array $config = [])
    {
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function redirect(?string $redirectUrl = null): string
    {
        return 'https://fake.test/oauth';
    }

    public function userFromCode(string $code): UserInterface
    {
        throw new \RuntimeException('not supported');
    }

    public function userFromToken(string $token): UserInterface
    {
        throw new \RuntimeException('not supported');
    }

    public function withRedirectUrl(string $redirectUrl): self
    {
        return $this;
    }

    public function withState(string $state): self
    {
        return $this;
    }

    public function scopes(array $scopes): self
    {
        return $this;
    }

    public function with(array $parameters): self
    {
        return $this;
    }

    public function withScopeSeparator(string $scopeSeparator): self
    {
        return $this;
    }

    public function getClientId(): ?string
    {
        return null;
    }

    public function getClientSecret(): ?string
    {
        return null;
    }
}