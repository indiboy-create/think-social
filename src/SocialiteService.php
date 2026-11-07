<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial;

use think\Service;

/**
 * think-social 服务提供者。
 */
class SocialiteService extends Service
{
    public function register(): void
    {
        $this->mergeConfig();

        $this->app->bind('socialite', Manager::class);
    }

    public function boot(): void
    {
        if ($this->app->config->get('socialite.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../route/socialite.php');
        }
    }

    /**
     * 合并包内默认配置与应用配置。
     */
    protected function mergeConfig(): void
    {
        $defaults = require __DIR__ . '/../config/socialite.php';
        $config   = (array) $this->app->config->get('socialite', []);

        $this->app->config->set(array_replace_recursive($defaults, $config), 'socialite');
    }
}