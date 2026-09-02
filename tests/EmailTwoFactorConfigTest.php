<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use YiiRocks\Voyti\TwoFactor\Email\EmailTwoFactorConfig;

final class EmailTwoFactorConfigTest extends TestCase
{
    /**
     * @return array<string, array{codeLifespan: int, maxAttempts: int}>
     */
    public static function invalidConfigurationProvider(): array
    {
        return [
            'zero lifespan' => ['codeLifespan' => 0, 'maxAttempts' => 1],
            'negative lifespan' => ['codeLifespan' => -1, 'maxAttempts' => 1],
            'zero attempts' => ['codeLifespan' => 1, 'maxAttempts' => 0],
            'negative attempts' => ['codeLifespan' => 1, 'maxAttempts' => -1],
        ];
    }

    public function testAcceptsPositiveConfiguration(): void
    {
        $config = new EmailTwoFactorConfig(1, 1);

        self::assertSame(1, $config->codeLifespan);
        self::assertSame(1, $config->maxAttempts);
    }

    #[DataProvider('invalidConfigurationProvider')]
    public function testRejectsNonPositiveConfiguration(int $codeLifespan, int $maxAttempts): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EmailTwoFactorConfig($codeLifespan, $maxAttempts);
    }
}
