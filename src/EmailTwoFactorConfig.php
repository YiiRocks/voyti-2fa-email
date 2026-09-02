<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email;

use InvalidArgumentException;

final readonly class EmailTwoFactorConfig
{
    public function __construct(
        public int $codeLifespan,
        public int $maxAttempts,
    ) {
        if ($codeLifespan < 1 || $maxAttempts < 1) {
            throw new InvalidArgumentException('Email two-factor lifespan and attempts must be positive.');
        }
    }
}
