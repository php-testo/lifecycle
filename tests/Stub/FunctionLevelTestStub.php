<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Stub;

use Testo\Assert;
use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * Stub mirroring {@see ClassLevelTestStub} for a function-based test case.
 *
 * The four lifecycle functions carry no {@see Test} attribute, so they are not tests; only
 * {@see stubRealTest()} is. If the {@see \Testo\Lifecycle\Internal\LifecycleInterceptor} supports
 * function-based cases, the hooks are invoked around the test, and the test observes their effect.
 * State is shared through {@see FunctionLevelTestState} because functions have no `$this`.
 */
#[BeforeClass]
function stubSetUpClass(): void
{
    ++FunctionLevelTestState::$beforeClassCalls;
}

#[AfterClass]
function stubTearDownClass(): void
{
    ++FunctionLevelTestState::$afterClassCalls;
}

#[BeforeTest]
function stubSetUp(): void
{
    ++FunctionLevelTestState::$beforeTestCalls;
}

#[AfterTest]
function stubTearDown(): void
{
    ++FunctionLevelTestState::$afterTestCalls;
}

#[Test]
function stubRealTest(): void
{
    Assert::same(FunctionLevelTestState::$beforeTestCalls, 1);
    Assert::same(FunctionLevelTestState::$beforeClassCalls, 1);
}

final class FunctionLevelTestState
{
    public static int $beforeTestCalls = 0;
    public static int $afterTestCalls = 0;
    public static int $beforeClassCalls = 0;
    public static int $afterClassCalls = 0;
}
