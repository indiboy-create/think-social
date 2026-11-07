<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Contract;

use Overtrue\Socialite\Contracts\UserInterface;

/**
 * 自定义回调处理类需要实现的接口。
 *
 * 在 config/socialite.php 中配置 handler 为该接口的实现类名。
 */
interface UserHandler
{
    /**
     * @param  string        $driver 驱动名称，例如 wechat
     * @param  UserInterface $user   第三方用户信息
     * @return mixed 返回 Response 时直接响应，否则以 JSON 返回
     */
    public function handle(string $driver, UserInterface $user): mixed;
}