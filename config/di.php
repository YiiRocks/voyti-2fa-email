<?php

declare(strict_types=1);

use YiiRocks\Voyti\TwoFactor\Email\Controller\EmailController;
use YiiRocks\Voyti\TwoFactor\Email\EmailTwoFactorMethod;
use YiiRocks\Voyti\TwoFactor\Email\Service\EmailCodeGeneratorService;
use YiiRocks\Voyti\TwoFactor\Email\Validator\EmailValidator;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\Message\Php\MessageSource;
use Yiisoft\Translator\SimpleMessageFormatter;

/** @var array $params */

return [
    EmailValidator::class => EmailValidator::class,
    EmailCodeGeneratorService::class => EmailCodeGeneratorService::class,

    // Registers the email method with the core registry via the `voyti.two-factor-method` tag.
    EmailTwoFactorMethod::class => [
        'class' => EmailTwoFactorMethod::class,
        'tags' => ['voyti.two-factor-method'],
    ],
    EmailController::class => EmailController::class,

    // Translation category source for this package's message files.
    'yiirocks/voyti-2fa-email.translator' => [
        'definition' => static fn() => new CategorySource(
            'voyti-2fa-email',
            new MessageSource(dirname(__DIR__) . '/resources/messages'),
            new SimpleMessageFormatter(),
        ),
        'tags' => ['translation.categorySource'],
    ],
];
