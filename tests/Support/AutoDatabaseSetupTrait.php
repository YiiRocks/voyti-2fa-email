<?php

declare(strict_types=1);

namespace YiiRocks\Voyti\TwoFactor\Email\tests\Support;

/**
 * Automatically calls setUpDatabase() and tearDownDatabase() without requiring
 * manual setUp/tearDown overrides. Use this instead of DatabaseSetupTrait
 * when tests just need database cleanup handled automatically.
 */
trait AutoDatabaseSetupTrait
{
    use DatabaseSetupTrait;

    protected function setUp(): void
    {
        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        $this->tearDownDatabase();
    }
}
