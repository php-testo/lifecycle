<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\BeforeTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test for lifecycle priority ordering on top-level functions.
 *
 * Higher-priority hook functions run first, exactly as for class methods.
 */
#[BeforeTest(priority: 10)]
function highPriorityBeforeFn(): void
{
    PriorityFunctionState::$log[] = 'high';
}

#[BeforeTest(priority: 0)]
function defaultPriorityBeforeFn(): void
{
    PriorityFunctionState::$log[] = 'default';
}

#[BeforeTest(priority: -10)]
function lowPriorityBeforeFn(): void
{
    PriorityFunctionState::$log[] = 'low';
}

/**
 * Higher-priority {@see BeforeTest} functions run before lower-priority ones,
 * so the log is ordered high → default → low.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(LifecycleInterceptor::class)]
function beforeFunctionsRunInPriorityOrder(): void
{
    $highIndex = \array_search('high', PriorityFunctionState::$log, true);
    $defaultIndex = \array_search('default', PriorityFunctionState::$log, true);
    $lowIndex = \array_search('low', PriorityFunctionState::$log, true);

    Assert::true($highIndex < $defaultIndex);
    Assert::true($defaultIndex < $lowIndex);
}

final class PriorityFunctionState
{
    /** @var list<string> */
    public static array $log = [];
}
