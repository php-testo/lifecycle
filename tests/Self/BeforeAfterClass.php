<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-tests for {@see BeforeClass} and {@see AfterClass} lifecycle attributes.
 */
#[Test]
#[Covers(BeforeClass::class)]
#[Covers(AfterClass::class)]
#[Covers(LifecycleInterceptor::class)]
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

    /**
     * The {@see BeforeClass} hook runs before the first test of the case.
     */
    public function beforeClassRunsBeforeFirstTest(): void
    {
        Assert::true(\in_array('beforeAll', self::$log, true));
        self::$log[] = 'test1';
    }

    /**
     * The {@see BeforeClass} hook runs exactly once for the whole case, not once per test.
     */
    public function beforeClassRunsOnlyOnce(): void
    {
        $beforeAllCount = \array_count_values(self::$log)['beforeAll'] ?? 0;
        Assert::same($beforeAllCount, 1);
        self::$log[] = 'test2';
    }
}
