<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\tests;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\SimpleCache\CacheInterface;
use YiiRocks\Voyti\TwoFactor\Email\EmailTwoFactorMethod;
use YiiRocks\Voyti\TwoFactor\Email\Service\EmailCodeGeneratorService;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\FakeUrlGenerator;
use YiiRocks\Voyti\TwoFactor\Email\tests\Support\MailCapture;
use YiiRocks\Voyti\TwoFactor\Email\Validator\EmailValidator;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection as SqliteConnection;
use Yiisoft\Db\Sqlite\Driver as SqliteDriver;
use Yiisoft\Db\Sqlite\Dsn;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\Message\Php\MessageSource;
use Yiisoft\Translator\SimpleMessageFormatter;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\TranslatorInterface;

abstract class TestCase extends BaseTestCase
{
    private static ?TranslatorInterface $translator = null;

    protected function createEmailCodeGeneratorService(?MailerInterface $mailer = null): EmailCodeGeneratorService
    {
        return new EmailCodeGeneratorService($mailer ?? $this->createMailCapture(), $this->createTranslator());
    }

    protected function createEmailTwoFactorMethod(?MailCapture $mailer = null): EmailTwoFactorMethod
    {
        return new EmailTwoFactorMethod(
            $this->createEmailValidator(),
            $this->createEmailCodeGeneratorService($mailer),
        );
    }

    protected function createEmailValidator(): EmailValidator
    {
        return new EmailValidator($this->createTranslator());
    }

    protected function createFakeUrlGenerator(): FakeUrlGenerator
    {
        return new FakeUrlGenerator();
    }

    protected function createMailCapture(): MailCapture
    {
        return new MailCapture();
    }

    protected function createSqliteConnection(): ConnectionInterface
    {
        $dsn = new Dsn('sqlite', ':memory:');
        $driver = new SqliteDriver($dsn);
        $cache = $this->createStub(CacheInterface::class);
        $cache->method('set')->willReturn(true);
        $cache->method('get')->willReturn(null);
        $schemaCache = new SchemaCache($cache);
        $schemaCache->setEnabled(false);
        return new SqliteConnection($driver, $schemaCache);
    }

    protected function createTranslator(string $locale = 'en'): TranslatorInterface
    {
        if (self::$translator === null) {
            $translator = new Translator($locale, null, 'voyti');
            $translator->addCategorySources(
                new CategorySource(
                    'voyti',
                    new MessageSource(InstalledVersions::getInstallPath('yiirocks/voyti') . '/resources/messages'),
                    new SimpleMessageFormatter(),
                ),
                new CategorySource(
                    'voyti-2fa',
                    new MessageSource(InstalledVersions::getInstallPath('yiirocks/voyti-2fa') . '/resources/messages'),
                    new SimpleMessageFormatter(),
                ),
                new CategorySource(
                    'voyti-2fa-email',
                    new MessageSource(dirname(__DIR__) . '/resources/messages'),
                    new SimpleMessageFormatter(),
                ),
            );
            self::$translator = $translator;
        }

        return self::$translator;
    }
}
