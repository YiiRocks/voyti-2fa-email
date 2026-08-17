<?php

declare(strict_types=1);

use YiiRocks\Voyti\TwoFactor\Email\Controller\EmailController;
use Yiisoft\Router\Route;

return [
    'yiirocks/voyti' => [
        'twoFactorMethodRoutes' => [
            Route::get('two-factor/email/')
                ->name('voyti/user-two-factor-email')
                ->action([EmailController::class, 'settings']),
            Route::post('two-factor/email/send-code')
                ->name('voyti/user-two-factor-email-send-code')
                ->action([EmailController::class, 'sendCode']),
        ],
    ],
];
