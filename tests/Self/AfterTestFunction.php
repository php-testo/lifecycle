<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test proving an {@see AfterTest} hook declared on a top-level function runs.
 *
 * As with the class-based variant, the "after" hook cannot be seen from the test it follows, so the
 * proof is split across two test functions: the first triggers the hook, the second (declared later,
 * run later) asserts it fired in between.
 */
#[AfterTest]
function afterTestHookFn(): void
{
    AfterTestFunctionState::$log[] = 'after';
}

/**
 * The first test only lets its {@see AfterTest} hook run afterwards.
 */
#[Test]
#[Covers(AfterTest::class)]
#[Covers(LifecycleInterceptor::class)]
function afterTestFunctionFirstTriggers(): void
{
    Assert::true(true);
}

/**
 * By the time the second test runs, the first test's {@see AfterTest} hook has already fired.
 */
#[Test]
#[Covers(AfterTest::class)]
#[Covers(LifecycleInterceptor::class)]
function afterTestFunctionSecondSeesPriorAfter(): void
{
    Assert::true(\in_array('after', AfterTestFunctionState::$log, true));
}

final class AfterTestFunctionState
{
    /** @var list<string> */
    public static array $log = [];
}
