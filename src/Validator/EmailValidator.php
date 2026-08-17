<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\Validator;

use YiiRocks\Voyti\Model\User;
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
    ) {}

    public function getErrorMessage(): string
    {
        return $this->error;
    }

    public function validate(User $user, string $code): bool
    {
        $storedCode = UserTwoFactor::forUser($user)->getSecret() ?? '';
        if ($storedCode === '') {
            $this->error = $this->translator->translate(
                'voyti-2fa-email.validator.two_factor_not_configured',
                category: 'voyti-2fa-email',
            );
            return false;
        }
        return hash_equals($storedCode, $code);
    }
}
