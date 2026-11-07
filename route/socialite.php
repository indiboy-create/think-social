<?php

use think\facade\Route;
use Youvision\ThinkSocial\Controller\SocialiteController;

$prefix     = trim((string) config('socialite.routes.prefix', 'socialite'), '/');
$middleware = (array) config('socialite.routes.middleware', []);

Route::group($prefix, function () {
    Route::get('<driver>/callback', SocialiteController::class . '@callback');
    Route::get('<driver>', SocialiteController::class . '@redirect');
})->middleware($middleware);