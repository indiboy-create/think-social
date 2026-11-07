<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Controller;

use Overtrue\Socialite\Contracts\UserInterface;
use think\App;
use think\Response;
use Throwable;
use Youvision\ThinkSocial\Event\UserResolved;
use Youvision\ThinkSocial\Manager;

/**
 * 内置的社交登录控制器。
 *
 * 如果业务需要自定义登录逻辑，可以关闭内置路由后自行实现控制器，
 * 或配置 socialite.handler 指定用户处理类。
 */
class SocialiteController
{
    public function __construct(protected App $app, protected Manager $socialite)
    {
    }

    /**
     * 跳转到第三方授权页面。
     */
    public function redirect(string $driver): Response
    {
        $url = $this->socialite->redirect($driver, $this->socialite->callbackUrl($driver));

        return redirect($url);
    }

    /**
     * 处理第三方回调。
     */
    public function callback(string $driver): Response
    {
        $error = $this->app->request->get('error');

        if (! empty($error)) {
            return json([
                'error'   => $error,
                'message' => (string) $this->app->request->get('error_description', ''),
            ], 400);
        }

        try {
            $user = $this->socialite->user($driver);
        } catch (Throwable $e) {
            $this->app->log->error(sprintf('[think-social] %s callback failed: %s', $driver, $e->getMessage()));

            return json([
                'error'   => 'authorization_failed',
                'message' => $e->getMessage(),
            ], 400);
        }

        event(new UserResolved($driver, $user));

        $handler = $this->app->config->get('socialite.handler');

        if (! empty($handler)) {
            $result = $this->app->make($handler)->handle($driver, $user);

            return $result instanceof Response ? $result : json($result);
        }

        return json($this->formatUser($driver, $user));
    }

    /**
     * 默认返回的用户数据结构。
     */
    protected function formatUser(string $driver, UserInterface $user): array
    {
        return [
            'driver'   => $driver,
            'id'       => $user->getId(),
            'nickname' => $user->getNickname(),
            'name'     => $user->getName(),
            'email'    => $user->getEmail(),
            'avatar'   => $user->getAvatar(),

        ];
    }
}