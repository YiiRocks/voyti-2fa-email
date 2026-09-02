<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\tests;

use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use YiiRocks\Voyti\TwoFactor\Email\EmailTwoFactorConfig;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\AutoDatabaseSetupTrait;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\UserFactoryTrait;
use YiiRocks\Voyti\TwoFactor\Model\UserTwoFactor;
use Yiisoft\Mailer\MailerInterface;

final class EmailTwoFactorMethodTest extends TestCase
{
    use AutoDatabaseSetupTrait;
    use UserFactoryTrait;

    public function testCodeGenerationResetsAttempts(): void
    {
        $user = $this->createUser();
        $code = $this->createEmailCodeGeneratorService()->run($user);
        $validator = $this->createEmailValidator(new EmailTwoFactorConfig(300, 2));

        self::assertFalse($validator->validate($user, '000000'));
        self::assertFalse($validator->validate($user, '000000'));
        self::assertFalse($validator->validate($user, $code));
    }

    public function testCodeGenerationStoresAndSends(): void
    {
        $user = $this->createUser(email: 'user@example.com');
        $mailer = $this->createMailCapture();
        $service = $this->createEmailCodeGeneratorService($mailer);
        $twoFactor = $this->createUserTwoFactor((int) $user->getId(), secret: 'expired');
        $twoFactor->setSecretCreatedAt(null);
        $twoFactor->setSecretAttempts(1);
        $twoFactor->save();

        $code = $service->run($user);

        self::assertSame(6, strlen($code));
        self::assertMatchesRegularExpression('/^\d{6}$/', $code);
        self::assertSame($code, UserTwoFactor::forUser($user)->getSecret());

        $message = $mailer->getLastMessage();
        self::assertNotNull($message);
        self::assertSame('Your verification code', (string) $message->getSubject());
        $body = (string) $message->getHtmlBody() . (string) $message->getTextBody();
        self::assertStringContainsString($code, $body);

        $validator = $this->createEmailValidator(new EmailTwoFactorConfig(300, 1));
        self::assertTrue($validator->validate($user, $code));
    }

    public function testCodeGenerationStoresCodeEvenWhenMailerThrows(): void
    {
        $user = $this->createUser();
        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new RuntimeException('SMTP down'));
        $service = $this->createEmailCodeGeneratorService($mailer);

        $code = $service->run($user);

        self::assertSame($code, UserTwoFactor::forUser($user)->getSecret());
    }

    public function testMetadata(): void
    {
        $method = $this->createEmailTwoFactorMethod();
        $translator = $this->createTranslator();
        $url = $this->createFakeUrlGenerator();

        self::assertSame('email', $method->getName());
        self::assertTrue($method->isAvailable());
        self::assertTrue($method->isCodeBased());
        self::assertTrue($method->requiresCodeDelivery());
        self::assertNull($method->getConfirmFragmentUrl($url));
        self::assertNull($method->getReauthFragmentUrl($url));
        self::assertSame('Email', $method->getButtonLabel($translator));
        self::assertSame('email', $method->getEnabledWithMethodName($translator));
        self::assertSame('//voyti/user-two-factor-email', $method->getSettingsUrl($url));
        $method->onDisable($this->createUser());
    }

    public function testOnAuthenticationStepStartDeliversCode(): void
    {
        $user = $this->createUser(email: 'step@example.com');
        $mailer = $this->createMailCapture();
        $method = $this->createEmailTwoFactorMethod($mailer);

        $method->onAuthenticationStepStart($user);

        self::assertNotNull($mailer->getLastMessage());
        self::assertNotNull(UserTwoFactor::forUser($user)->getSecret());
    }

    public function testValidatorRejectsExpiredAndExhaustedCodes(): void
    {
        $user = $this->createUser();
        $twoFactor = $this->createUserTwoFactor((int) $user->getId(), secret: '654321');
        $validator = $this->createEmailValidator(new EmailTwoFactorConfig(300, 2));

        $twoFactor->setSecretCreatedAt(time() - 301);
        $twoFactor->save();

        self::assertFalse($validator->validate($user, '654321'));
        self::assertSame('Invalid verification code.', $validator->getErrorMessage());

        $twoFactor->setSecretCreatedAt(time());
        $twoFactor->setSecretAttempts(0);
        $twoFactor->save();

        self::assertFalse($validator->validate($user, '000000'));
        self::assertFalse($validator->validate($user, '000000'));
        self::assertFalse($validator->validate($user, '654321'));
        self::assertSame('Invalid verification code.', $validator->getErrorMessage());
    }

    #[DataProvider('verifyProvider')]
    public function testVerify(array $input, bool $expected, ?string $storedSecret = '654321', ?string $expectedError = ''): void
    {
        $user = $this->createUser();
        $this->createUserTwoFactor((int) ($user->getId() ?? 0), enabled: false, method: 'email', secret: $storedSecret);
        $method = $this->createEmailTwoFactorMethod();

        self::assertSame($expected, $method->verify($user, $input));
        self::assertSame($expectedError, $method->getErrorMessage());
    }

    /**
     * @return array<array{input: array, expected: bool, storedSecret?: ?string, expectedError?: string}>
     */
    public static function verifyProvider(): array
    {
        return [
            'correct code' => [['code' => '654321'], true],
            'wrong code' => [['code' => 'nope'], false, '654321', 'Invalid verification code.'],
            'missing code key' => [[], false, '654321', 'Invalid verification code.'],
            'no stored code with submission' => [['code' => '123456'], false, null, 'Email two factor authentication is not configured.'],
            'no stored code with empty submission' => [['code' => ''], false, null, 'Email two factor authentication is not configured.'],
        ];
    }
}
