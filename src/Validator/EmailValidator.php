<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\Validator;

use YiiRocks\Voyti\Model\User;
use YiiRocks\Voyti\TwoFactor\Email\EmailTwoFactorConfig;
use YiiRocks\Voyti\TwoFactor\Model\UserTwoFactor;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Verifies email-delivered two-factor authentication codes by comparing against the user's stored
 * code, exposing a translated error message via {@see getErrorMessage()} on failure.
 */
final class EmailValidator
{
    private string $error = '';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EmailTwoFactorConfig $config,
    ) {}

    public function getErrorMessage(): string
    {
        return $this->error;
    }

    public function validate(User $user, string $code): bool
    {
        $twoFactor = UserTwoFactor::forUser($user);
        $storedCode = $twoFactor->getSecret() ?? '';
        if ($storedCode === '') {
            $this->error = $this->translator->translate(
                'voyti-2fa-email.validator.two_factor_not_configured',
                category: 'voyti-2fa-email',
            );
            return false;
        }

        if (!$twoFactor->recordEmailAttempt($this->config->codeLifespan, $this->config->maxAttempts)) {
            $this->error = $this->translator->translate(
                'voyti-2fa.validator.invalid_verification_code',
                category: 'voyti-2fa',
            );
            return false;
        }
        if (!hash_equals($storedCode, $code)) {
            $this->error = $this->translator->translate(
                'voyti-2fa.validator.invalid_verification_code',
                category: 'voyti-2fa',
            );
            return false;
        }
        return true;
    }
}
