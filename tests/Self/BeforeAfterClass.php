<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\BeforeClass;
use Testo\Test;

/**
 * Self-tests for {@see BeforeClass} and {@see AfterClass} lifecycle attributes.
 */
final class BeforeAfterClass
{
    /** @var list<string> */
    public static array $log = [];

    #[BeforeClass]
    public static function setupOnce(): void
    {
        self::$log[] = 'beforeAll';
    }

    #[AfterClass]
    public static function teardownOnce(): void
    {
        self::$log[] = 'afterAll';
    }

    #[Test]
    public function firstTest(): void
    {
        // BeforeClass should have been called once
        Assert::true(\in_array('beforeAll', self::$log, true));
        self::$log[] = 'test1';
    }

    #[Test]
    public function secondTest(): void
    {
        // BeforeClass should still be called only once (not twice)
        $beforeAllCount = \array_count_values(self::$log)['beforeAll'] ?? 0;
        Assert::same($beforeAllCount, 1);
        self::$log[] = 'test2';
    }
}
