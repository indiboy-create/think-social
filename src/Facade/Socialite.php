<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Facade;

use think\Facade;

/**
 * @method static \Overtrue\Socialite\Contracts\ProviderInterface driver(?string $driver = null)
 * @method static \Overtrue\Socialite\Contracts\UserInterface user(?string $driver = null, ?string $code = null, ?string $state = null)
 * @method static string redirect(?string $driver = null, ?string $redirectUrl = null)
 * @method static \Youvision\ThinkSocial\Manager extend(string $name, \Closure $callback)
 * @method static \Youvision\ThinkSocial\Manager config(\Overtrue\Socialite\Config $config)
 * @method static array getResolvedProviders()
 * @method static array getDriverConfig(string $driver)
 *
 * @see \Youvision\ThinkSocial\Manager
 */
class Socialite extends Facade
{
    protected static function getFacadeClass()
    {
        return 'socialite';
    }
}