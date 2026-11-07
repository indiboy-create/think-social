<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Event;

use Overtrue\Socialite\Contracts\UserInterface;

/**
 * 成功获取第三方用户信息后触发。
 */
class UserResolved
{
    public function __construct(
        public string $driver,
        public UserInterface $user
    ) {
    }
}