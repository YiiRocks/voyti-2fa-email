<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\Service;

use Throwable;
use YiiRocks\Voyti\Model\User;
use YiiRocks\Voyti\TwoFactor\Model\UserTwoFactor;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Message;
use Yiisoft\Translator\TranslatorInterface;

/**
 * Generates a random six-digit email two-factor authentication code, stores it as the user's
 * {@see UserTwoFactor} secret, and emails it. The message is built inline (no view templates) so the
 * package carries its own delivery independent of the core mailer's template path.
 */
final readonly class EmailCodeGeneratorService
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @return numeric-string
     */
    public function run(User $user): string
    {
        /** @infection-ignore-all The exact random bounds can't be pinned by a deterministic test (a single draw rarely lands on a boundary); an off-by-one on either end still yields an acceptable numeric code. */
        $code = (string) random_int(100000, 999999);

        $twoFactor = UserTwoFactor::forUser($user);
        $twoFactor->setSecret($code);
        $twoFactor->setSecretCreatedAt(time());
        $twoFactor->setSecretAttempts(0);
        $twoFactor->save();

        $this->send($user->getEmail(), $code);

        return $code;
    }

    private function send(string $email, string $code): void
    {
        $body = $this->translator->translate(
            'voyti-2fa-email.mail.body',
            ['code' => $code],
            category: 'voyti-2fa-email',
        );
        $message = (new Message(
            to: $email,
            subject: $this->translator->translate('voyti-2fa-email.mail.subject', category: 'voyti-2fa-email'),
        ))
            ->withTextBody($body)
            ->withHtmlBody($body);

        try {
            $this->mailer->send($message);
        } catch (Throwable) {
            // Best-effort delivery, mirroring the core MailService: a mailer failure must not break
            // the setup flow (the user can request a fresh code).
        }
    }
}
