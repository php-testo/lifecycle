<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test for {@see BeforeClass} declared on a top-level function.
 *
 * For a function-based case the "class" hook applies to the whole file: it must run once before the
 * first test of the case, not once per test.
 */
#[BeforeClass]
function beforeClassHookFn(): void
{
    BeforeClassFunctionState::$log[] = 'beforeAll';
}

/**
 * The {@see BeforeClass} hook runs before the first test of the case.
 */
#[Test]
#[Covers(BeforeClass::class)]
#[Covers(LifecycleInterceptor::class)]
function beforeClassFunctionRunsBeforeFirstTest(): void
{
    Assert::true(\in_array('beforeAll', BeforeClassFunctionState::$log, true));
    BeforeClassFunctionState::$log[] = 'test1';
}

/**
 * The {@see BeforeClass} hook runs exactly once for the whole case, not once per test.
 */
#[Test]
#[Covers(BeforeClass::class)]
#[Covers(LifecycleInterceptor::class)]
function beforeClassFunctionRunsOnlyOnce(): void
{
    $beforeAllCount = \array_count_values(BeforeClassFunctionState::$log)['beforeAll'] ?? 0;
    Assert::same($beforeAllCount, 1);
}

final class BeforeClassFunctionState
{
    /** @var list<string> */
    public static array $log = [];
}
