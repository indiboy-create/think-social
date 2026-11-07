<?php

/*
 * think-social 配置文件
 *
 * 所有驱动配置项最终都会传给 overtrue/socialite 的 Provider。
 * 通用字段说明：
 * - client_id：应用 AppID / Client ID（旧版本字段 app_id 亦可使用）
 * - client_secret：应用 AppSecret / Client Secret（旧版本字段 app_secret 亦可使用）
 * - redirect：授权回调地址，使用内置控制器时会自动覆盖为当前请求的回调地址
 * - scopes：授权范围，通常为数组，具体取值参考各平台文档
 * - guzzle：Guzzle 请求选项，常用于设置 timeout、verify、proxy 等
 */

return [
    /*
     * 默认驱动名称。
     * 调用 Socialite::driver() 未传参数时使用该驱动，需与 drivers 中的键一致。
     */
    'default' => 'wechat',

    /*
     * 是否关闭 state 校验。
     * false：跳转授权时生成随机 state 并写入 Session，回调时校验，可防止 CSRF；
     * true：完全忽略 state，适合无 Session 的纯 API 场景。
     * 注意：开启校验时需要应用启用 Session（全局中间件 SessionInit）。
     */
    'stateless' => false,

    /*
     * 所有驱动共享的默认配置，会与 drivers 中每个驱动的配置递归合并。
     * 例如 timeout 可以统一设置，单个驱动仍可单独覆盖。
     */
    'defaults' => [
        'guzzle' => [
            // 请求超时时间（秒），避免第三方接口无响应导致进程阻塞
            'timeout' => 10,
            // 是否校验 SSL 证书，生产环境务必保持 true
            'verify'  => true,
        ],
    ],

    /*
     * 回调处理类，需实现 \Youvision\ThinkSocial\Contract\UserHandler 接口。
     * 为空时，内置控制器会直接以 JSON 返回第三方用户信息。
     * 示例：'handler' => \app\social\UserLoginHandler::class
     */
    'handler' => null,

    /*
     * 内置路由配置。
     */
    'routes' => [
        // 是否自动注册内置路由；使用自定义路由时设为 false
        'enabled'    => true,
        // 路由前缀，最终地址为 /{prefix}/{driver} 与 /{prefix}/{driver}/callback
        'prefix'     => 'socialite',
        // 路由中间件，例如 ['session'] 或 [\think\middleware\SessionInit::class]
        'middleware' => [],
        // 固定回调地址；为空时根据当前请求域名自动生成
        'callback'   => '',
    ],

    /*
     * 驱动配置，键名即为 Socialite::driver('name') 中的 name。
     * 内置驱动参考 overtrue/socialite，常用示例如下：
     *
     * 'wechat' => [
     *     'client_id'     => env('WECHAT_APP_ID', ''),
     *     'client_secret' => env('WECHAT_APP_SECRET', ''),
     *     'scopes'        => ['snsapi_login'],
     * ],
     * 'wechat_mp' => [
     *     'provider'      => 'wechat',
     *     'client_id'     => env('WECHAT_MP_APP_ID', ''),
     *     'client_secret' => env('WECHAT_MP_APP_SECRET', ''),
     *     'scopes'        => ['snsapi_userinfo'],
     * ],
     * 'qq' => [
     *     'client_id'     => env('QQ_APP_ID', ''),
     *     'client_secret' => env('QQ_APP_KEY', ''),
     *     'scopes'        => ['get_user_info'],
     * ],
     * 'github' => [
     *     'client_id'     => env('GITHUB_CLIENT_ID', ''),
     *     'client_secret' => env('GITHUB_CLIENT_SECRET', ''),
     *     'scopes'        => ['user:email'],
     * ],
     * 'weibo' => [
     *     'client_id'     => env('WEIBO_APP_ID', ''),
     *     'client_secret' => env('WEIBO_APP_SECRET', ''),
     * ],
     *
     * 如需使用自定义 Provider 类，可设置 'provider' => 完整类名，
     * 该类需实现 \Overtrue\Socialite\Contracts\ProviderInterface。
     */
    'drivers' => [],
];