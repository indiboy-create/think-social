## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# think-social

基于 [overtrue/socialite](https://github.com/overtrue/socialite) 的 ThinkPHP 8 社交登录扩展包。

## 环境要求

- PHP >= 8.0
- ThinkPHP >= 8.0
- overtrue/socialite ^4.0

## 安装

```bash
composer require youvision/think-social
```

安装后执行服务发现：

```bash
php think service:discover
```

## 配置

将 `vendor/youvision/think-social/config/socialite.php` 复制到应用的 `config/socialite.php`，按需填写驱动：

```php
return [
    'default'   => 'wechat',
    'stateless' => false,

    'routes' => [
        'enabled'    => true,
        'prefix'     => 'socialite',
        'middleware' => [],
        'callback'   => '',
    ],

    'drivers' => [
        'wechat' => [
            'client_id'     => env('WECHAT_APP_ID'),
            'client_secret' => env('WECHAT_APP_SECRET'),
            'scopes'        => ['snsapi_login'],
        ],
        'qq' => [
            'client_id'     => env('QQ_APP_ID'),
            'client_secret' => env('QQ_APP_KEY'),
            'scopes'        => ['get_user_info'],
        ],
        'github' => [
            'client_id'     => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
        ],
    ],
];
```

每个配置项的完整说明见包内 `config/socialite.php` 注释。

> `defaults` 中的配置会与每个驱动递归合并，可统一设置 `guzzle.timeout`、`guzzle.verify` 等。

## 内置路由

开启 `routes.enabled` 后自动注册：

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | `/socialite/{driver}` | 跳转第三方授权页 |
| GET | `/socialite/{driver}/callback` | 第三方回调 |

在第三方平台配置的回调地址为：`https://你的域名/socialite/{driver}/callback`。

关闭内置路由后，可在应用路由中自行定义：

```php
use think\facade\Route;
use Youvision\ThinkSocial\Controller\SocialiteController;

Route::get('oauth/<driver>', SocialiteController::class . '@redirect');
Route::get('oauth/<driver>/callback', SocialiteController::class . '@callback');
```

## 基本使用

```php
use Youvision\ThinkSocial\Facade\Socialite;

// 跳转授权页
$url = Socialite::driver('wechat')
    ->withRedirectUrl('https://example.com/socialite/wechat/callback')
    ->redirect();

return redirect($url);

// 回调换取用户信息
$user = Socialite::driver('wechat')->userFromCode($code);

$user->getId();        // 第三方用户 ID
$user->getNickname();  // 昵称
$user->getAvatar();    // 头像
$user->getRaw();       // 原始返回数据（可读取 unionid 等字段）
```

也可直接使用管理器的快捷方法：

```php
Socialite::redirect('wechat', $callbackUrl);
$user = Socialite::user('wechat'); // 自动从当前请求读取 code 与 state
```

## 自定义回调处理

实现 `Youvision\ThinkSocial\Contract\UserHandler`，并在配置中指定：

```php
use Overtrue\Socialite\Contracts\UserInterface;
use Youvision\ThinkSocial\Contract\UserHandler;

class UserLoginHandler implements UserHandler
{
    public function handle(string $driver, UserInterface $user): mixed
    {
        // 查询或创建本地用户、写入登录态
        return ['token' => $token];
    }
}
```

```php
'handler' => \app\social\UserLoginHandler::class,
```

未配置 `handler` 时，回调接口以 JSON 返回用户信息。

## 事件

获取用户信息成功后触发 `Youvision\ThinkSocial\Event\UserResolved`，可在应用 `app/event.php` 中监听：

```php
use Youvision\ThinkSocial\Event\UserResolved;

'listen' => [
    UserResolved::class => [\app\listener\SocialLogin::class],
],
```

## 自定义 Provider

实现 `Overtrue\Socialite\Contracts\ProviderInterface`，然后注册：

```php
Socialite::extend('custom', function (array $config) {
    return new \app\social\CustomProvider($config);
});
```

## 多应用模式

多应用模式下建议将 `routes.enabled` 设为 `false`，在各应用的路由文件中自行注册控制器路由，同时为不同应用配置不同的 `default` 与 `drivers`。

## State 校验

`stateless` 为 `false` 时，授权跳转前会生成 state 并写入 Session，回调时校验并删除。

- 需要应用启用 Session（ThinkPHP 默认全局中间件 `\think\middleware\SessionInit::class`）。
- 纯 API 或无 Session 场景请设置 `'stateless' => true`，此时不再校验 state。

## License

MIT