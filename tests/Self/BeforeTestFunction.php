<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\BeforeTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test for {@see BeforeTest} declared on a top-level function.
 *
 * The file forms a single function-based test case: the hook function carries no {@see Test}
 * attribute, so it is not itself a test, yet it must run before the test body. Functions have no
 * `$this`, so state is shared through a static holder.
 */
#[BeforeTest]
function beforeTestHookFn(): void
{
    BeforeTestFunctionState::$log[] = 'before';
}

/**
 * The {@see BeforeTest} hook function runs before the test body, so the log already carries its
 * entry by the time the test executes.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(LifecycleInterceptor::class)]
function beforeTestFunctionRunsBeforeBody(): void
{
    Assert::true(\in_array('before', BeforeTestFunctionState::$log, true));
}

final class BeforeTestFunctionState
{
    /** @var list<string> */
    public static array $log = [];
}
