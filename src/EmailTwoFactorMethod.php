<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email;

use Override;
use YiiRocks\Voyti\Model\User;
use YiiRocks\Voyti\TwoFactor\Email\Service\EmailCodeGeneratorService;
use YiiRocks\Voyti\TwoFactor\Email\Validator\EmailValidator;
use YiiRocks\Voyti\TwoFactor\TwoFactorMethodInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Email-code two-factor method: mails a fresh six-digit code at the start of the authentication
 * step and verifies the user-typed code against the stored one via {@see EmailValidator}. Always
 * available since it relies only on the core mailer.
 */
final readonly class EmailTwoFactorMethod implements TwoFactorMethodInterface
{
    public function __construct(
        private EmailValidator $emailValidator,
        private EmailCodeGeneratorService $emailCodeGeneratorService,
    ) {}

    #[Override]
    public function getButtonLabel(TranslatorInterface $translator): string
    {
        return $translator->translate('voyti-2fa-email.view.two_factor_email.button_label', category: 'voyti-2fa-email');
    }

    #[Override]
    public function getConfirmFragmentUrl(UrlGeneratorInterface $url): ?string
    {
        return null;
    }

    #[Override]
    public function getEnabledWithMethodName(TranslatorInterface $translator): string
    {
        return $translator->translate('voyti-2fa-email.view.two_factor_email.method_name', category: 'voyti-2fa-email');
    }

    #[Override]
    public function getErrorMessage(): string
    {
        return $this->emailValidator->getErrorMessage();
    }

    #[Override]
    public function getName(): string
    {
        return 'email';
    }

    #[Override]
    public function getReauthFragmentUrl(UrlGeneratorInterface $url): ?string
    {
        // Code-based: the settings screen re-authenticates by prompting for a typed code inline.
        return null;
    }

    #[Override]
    public function getSettingsUrl(UrlGeneratorInterface $url): string
    {
        return $url->generate('voyti/user-two-factor-email');
    }

    #[Override]
    public function isAvailable(): bool
    {
        return true;
    }

    #[Override]
    public function isCodeBased(): bool
    {
        return true;
    }

    #[Override]
    public function onAuthenticationStepStart(User $user): void
    {
        $this->emailCodeGeneratorService->run($user);
    }

    #[Override]
    public function onDisable(User $user): void {}

    #[Override]
    public function requiresCodeDelivery(): bool
    {
        return true;
    }

    #[Override]
    public function verify(User $user, array $data): bool
    {
        return $this->emailValidator->validate($user, $data['code'] ?? '');
    }
}
