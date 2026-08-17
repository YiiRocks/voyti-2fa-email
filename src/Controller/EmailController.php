<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\Controller;

use Composer\InstalledVersions;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use YiiRocks\Voyti\Controller\RedirectTrait;
use YiiRocks\Voyti\Controller\RenderTrait;
use YiiRocks\Voyti\Helper\FlashType;
use YiiRocks\Voyti\Model\User;
use YiiRocks\Voyti\Service\FlashNotifier;
use YiiRocks\Voyti\TwoFactor\Email\Service\EmailCodeGeneratorService;
use YiiRocks\Voyti\TwoFactor\Form\TwoFactorCodeForm;
use YiiRocks\Voyti\TwoFactor\Helper\Views\IndexView;
use YiiRocks\Voyti\TwoFactor\Model\UserTwoFactor;
use YiiRocks\Voyti\TwoFactor\Service\BackupCodeService;
use YiiRocks\Voyti\TwoFactor\TwoFactorMethodInterface;
use YiiRocks\Voyti\TwoFactor\TwoFactorMethodRegistry;
use YiiRocks\Voyti\VoytiConfig;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Manages email two-factor setup for the current user: renders the confirm/send screen (or just its
 * fragment for the settings page's lazy-loading JavaScript) and emails a fresh code on request.
 * Enabling/disabling itself stays generic in voyti-2fa's `TwoFactorController`; the user's 2FA state
 * lives in {@see UserTwoFactor}.
 */
final readonly class EmailController
{
    use RedirectTrait;
    use RenderTrait;

    public function __construct(
        private TranslatorInterface $translator,
        private WebViewRenderer $viewRenderer,
        private UrlGeneratorInterface $url,
        private VoytiConfig $config,
        private CurrentUser $currentUser,
        private ResponseFactoryInterface $responseFactory,
        private FlashNotifier $flashNotifier,
        private BackupCodeService $backupCodeService,
        private TwoFactorMethodRegistry $twoFactorMethods,
        private EmailCodeGeneratorService $emailCodeGeneratorService,
    ) {}

    public function sendCode(): ResponseInterface
    {
        /** @var User $user */
        $user = $this->currentUser->getIdentity();

        if (UserTwoFactor::forUser($user)->isEnabled()) {
            return $this->enabledRedirect();
        }

        $this->emailCodeGeneratorService->run($user);

        $method = $this->twoFactorMethods->get('email');

        return $this->renderTwoFactorIndex(
            $user,
            $method,
            preloadedFragmentHtml: $this->renderSetupFragment($user, $method, emailCodeSent: true),
        );
    }

    public function settings(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $this->currentUser->getIdentity();

        if (UserTwoFactor::forUser($user)->isEnabled()) {
            return $this->redirect($this->url->generate('voyti/user-two-factor'));
        }

        $method = $this->twoFactorMethods->get('email');

        if (strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
            return $this->renderFragment('two-factor/_email', [
                'data' => [
                    'emailCodeSent' => false,
                    'userEmail' => $user->getEmail(),
                    'sendCodeUrl' => $this->url->generate('voyti/user-two-factor-email-send-code'),
                    /** @infection-ignore-all Only used when emailCodeSent is true; this code path has emailCodeSent=false. */
                    'enableUrl' => $this->url->generate('voyti/user-two-factor-enable'),
                ],
                'form' => new TwoFactorCodeForm($this->translator, $method->getName()),
            ]);
        }

        return $this->renderTwoFactorIndex(
            $user,
            $method,
            preloadedFragmentHtml: $this->renderSetupFragment($user, $method),
        );
    }

    /**
     * Absolute base path of the voyti-2fa package's views, where the generic `two-factor/index` page
     * this controller composes lives.
     */
    private function baseViewPath(): string
    {
        /** @var non-empty-string $basePath */
        $basePath = InstalledVersions::getInstallPath('yiirocks/voyti-2fa');

        return $basePath . '/resources/views/' . $this->config->webTheme->value;
    }

    /**
     * Absolute base path of the core module's views, whose shared chrome (menu, flash) the composed
     * `two-factor/index` page includes via its `$coreViews` variable.
     */
    private function coreViewPath(): string
    {
        /** @var non-empty-string $corePath */
        $corePath = InstalledVersions::getInstallPath('yiirocks/voyti');

        return $corePath . '/resources/views/' . $this->config->webTheme->value;
    }

    private function enabledRedirect(): ResponseInterface
    {
        $this->flashNotifier->add(
            FlashType::SUCCESS,
            $this->translator->translate('voyti-2fa.settings.two_factor_enabled', category: 'voyti-2fa'),
        );

        return $this->redirect($this->url->generate('voyti/user-two-factor'));
    }

    private function renderSetupFragment(
        User $user,
        TwoFactorMethodInterface $method,
        bool $emailCodeSent = false,
    ): string {
        return (string) $this->renderFragment('two-factor/_email', [
            'form' => new TwoFactorCodeForm($this->translator, $method->getName()),
            'data' => [
                'emailCodeSent' => $emailCodeSent,
                'userEmail' => $user->getEmail(),
                'sendCodeUrl' => $this->url->generate('voyti/user-two-factor-email-send-code'),
                'enableUrl' => $this->url->generate('voyti/user-two-factor-enable'),
            ],
        ])->getBody();
    }

    /**
     * @param array<string, list<string>> $errors
     */
    private function renderTwoFactorIndex(
        User $user,
        TwoFactorMethodInterface $method,
        array $errors = [],
        ?string $preloadedFragmentHtml = null,
    ): ResponseInterface {
        return $this->renderView('two-factor/index', [
            'coreViews' => $this->coreViewPath(),
            /** @infection-ignore-all The index template only uses `$form` in the enabled-user branch (disable form); this screen only ever shows non-enabled users, so the value is unobservable here. */
            'form' => new TwoFactorCodeForm($this->translator, $method->getName()),
            'data' => IndexView::create(
                UserTwoFactor::forUser($user)->isEnabled(),
                $method,
                $errors,
                /** @infection-ignore-all codeDelivered only affects the disable-confirmation flow, which needs an enabled user; this setup screen only ever shows non-enabled users, so the value is unobservable. */
                false,
                $this->backupCodeService->hasUnused($user),
                $preloadedFragmentHtml,
                $this->twoFactorMethods->getAvailable(),
                $this->config,
                $this->url,
                $this->translator(),
            ),
        ]);
    }

    /**
     * Shadows {@see RenderTrait::resolveViewPath()} to look in this package's bundled views first
     * (for the `two-factor/_email` fragment and its `_code-form` sibling), then the host's override
     * path, then voyti-2fa's views for the generic `two-factor/index` page it composes.
     */
    private function resolveViewPath(string $view): string
    {
        $pluginPath = dirname(__DIR__, 2) . '/resources/views/' . $this->config->webTheme->value;
        if (is_file($pluginPath . '/' . $view . '.php')) {
            return $pluginPath;
        }

        if ($this->config->viewPath !== null && is_file($this->config->viewPath . '/' . $view . '.php')) {
            return $this->config->viewPath;
        }

        return $this->baseViewPath();
    }
}
