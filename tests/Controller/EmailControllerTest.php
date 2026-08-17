<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\tests\Controller;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use YiiRocks\Voyti\Model\User;
use YiiRocks\Voyti\TwoFactor\Email\Controller\EmailController;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\AutoDatabaseSetupTrait;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\CurrentUserTrait;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\TestContainerTrait;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\UserFactoryTrait;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\VoytiConfigFactory;
use YiiRocks\Voyti\TwoFactor\Email\tests\TestCase;
use YiiRocks\Voyti\VoytiConfig;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\User\CurrentUser;

final class EmailControllerTest extends TestCase
{
    use AutoDatabaseSetupTrait;
    use CurrentUserTrait;
    use TestContainerTrait;
    use UserFactoryTrait;

    /**
     * @return array<array{alreadyEnabled: bool, expectedStatus: int, email?: string}>
     */
    public static function sendCodeProvider(): array
    {
        return [
            'delivers code and renders form' => [false, 200, 'sc@example.com'],
            'redirects when already enabled' => [true, 302],
        ];
    }

    public function testHostViewPathOverridesTheComposedIndexPage(): void
    {
        // The email fragment ships in this package, but a host can override the generic
        // `two-factor/index` page it composes via the configured viewPath.
        $overrideDir = sys_get_temp_dir() . '/voyti-2fa-email-viewpath-' . uniqid('', true);
        mkdir($overrideDir . '/two-factor', 0o777, true);
        file_put_contents($overrideDir . '/two-factor/index.php', '<?php echo "HOST-INDEX-OVERRIDE";');

        try {
            $user = $this->createUser();
            $container = $this->createTestContainer([
                CurrentUser::class => $this->createCurrentUser($user),
                VoytiConfig::class => VoytiConfigFactory::create(viewPath: $overrideDir),
            ]);

            $response = $container->get(EmailController::class)->settings(new ServerRequest('GET', '/'));

            self::assertStringContainsString('HOST-INDEX-OVERRIDE', (string) $response->getBody());
        } finally {
            @unlink($overrideDir . '/two-factor/index.php');
            @rmdir($overrideDir . '/two-factor');
            @rmdir($overrideDir);
        }
    }

    #[DataProvider('sendCodeProvider')]
    public function testSendCode(bool $alreadyEnabled, int $expectedStatus, ?string $email = null): void
    {
        $user = $this->createUser(email: $email ?? 'sc@example.com');
        if ($alreadyEnabled) {
            $this->createUserTwoFactor((int) ($user->getId() ?? 0), enabled: true, method: 'email');
        }
        [$container, $controller] = $this->build($user);

        $response = $controller->sendCode();

        self::assertSame($expectedStatus, $response->getStatusCode());

        if ($alreadyEnabled) {
            self::assertSame('//voyti/user-two-factor', $response->getHeaderLine('Location'));
            self::assertSame(
                'Two-factor authentication has been enabled',
                $container->get(FlashInterface::class)->get('success'),
            );
        } else {
            $body = (string) $response->getBody();
            self::assertStringContainsString('Enter the verification code sent to your email', $body);
            self::assertStringContainsString('data-voyti-2fa-method="email"', $body);
            /** @var MailerInterface&MailCapture $mailer */
            $mailer = $container->get(MailerInterface::class);
            self::assertNotNull($mailer->getLastMessage());
        }
    }

    public function testSettingsFallsBackToBaseIndexWhenOverrideDirLacksIt(): void
    {
        // viewPath is set but has no override for the composed `two-factor/index` page, so voyti-2fa's
        // own view is used (the package fragment still comes from this package).
        $emptyDir = sys_get_temp_dir() . '/voyti-2fa-email-empty-' . uniqid('', true);
        mkdir($emptyDir, 0o777, true);

        try {
            $user = $this->createUser(email: 'fallback@example.com');
            $container = $this->createTestContainer([
                CurrentUser::class => $this->createCurrentUser($user),
                VoytiConfig::class => VoytiConfigFactory::create(viewPath: $emptyDir),
            ]);

            $response = $container->get(EmailController::class)->settings(new ServerRequest('GET', '/'));
            $body = (string) $response->getBody();

            self::assertSame(200, $response->getStatusCode());
            self::assertStringContainsString('Two-Factor Authentication', $body);
            self::assertStringContainsString('fallback@example.com', $body);
            // Verify the email method button is present (tests that $methods array is not empty)
            self::assertStringContainsString('data-voyti-2fa-method="email"', $body);
        } finally {
            @rmdir($emptyDir);
        }
    }

    public function testSettingsRedirectsWhenAlreadyEnabled(): void
    {
        $user = $this->createUser();
        $this->createUserTwoFactor((int) ($user->getId() ?? 0), enabled: true, method: 'email');
        [, $controller] = $this->build($user);

        $response = $controller->settings(new ServerRequest('GET', '/'));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('//voyti/user-two-factor', $response->getHeaderLine('Location'));
    }

    public function testSettingsRendersFragmentForAjaxRequest(): void
    {
        $user = $this->createUser(email: 'ajax@example.com');
        [, $controller] = $this->build($user);

        $request = (new ServerRequest('GET', '/'))->withHeader('X-Requested-With', 'XMLHttpRequest');
        $response = $controller->settings($request);
        $body = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        // The bare fragment: the confirm/send step showing the user's email, no full-page chrome.
        self::assertStringContainsString('ajax@example.com', $body);
        self::assertStringContainsString('//voyti/user-two-factor-email-send-code', $body);
        self::assertStringNotContainsString('Two-Factor Authentication', $body);
    }

    public function testSettingsRendersFullPageWithPreloadedFragment(): void
    {
        $user = $this->createUser(email: 'full@example.com');
        [, $controller] = $this->build($user);

        $response = $controller->settings(new ServerRequest('GET', '/'));
        $body = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        // Full page: the shared account chrome plus the preloaded email setup fragment.
        self::assertStringContainsString('full@example.com', $body);
        self::assertStringContainsString('Two-Factor Authentication', $body);
        // Verify the email method button is present (tests that $methods array is not empty)
        self::assertStringContainsString('data-voyti-2fa-method="email"', $body);
    }

    /**
     * @return array{0: ContainerInterface, 1: EmailController}
     */
    private function build(User $user): array
    {
        $container = $this->createTestContainer([
            CurrentUser::class => $this->createCurrentUser($user),
        ]);

        return [$container, $container->get(EmailController::class)];
    }
}
