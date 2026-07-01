<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test for the interaction between class-level {@see Test} attribute and lifecycle methods.
 *
 * When {@see Test} is placed on the class, every public void/never method would normally be
 * picked up as a test by the test plugin. Methods marked with lifecycle attributes
 * (e.g. {@see BeforeTest}, {@see AfterTest}) must be filtered out of that set —
 * otherwise they get scheduled both as tests AND as lifecycle hooks, causing the
 * test set to be inflated and the lifecycle hooks to run more times than expected.
 *
 * The class declares its lifecycle methods first to ensure source order would surface
 * the bug: if the lifecycle methods leaked into the test set, the `setUp` body would
 * execute as a test before the real test method runs, leaving `$setUpCalls > 1`.
 */
#[Test]
#[Covers(LifecycleInterceptor::class)]
final class ClassLevelTestWithLifecycle
{
    private int $setUpCalls = 0;

    #[BeforeTest]
    public function setUp(): void
    {
        ++$this->setUpCalls;
    }

    #[AfterTest]
    public function tearDown(): void {}

    /**
     * Only this method is scheduled as a test, and its {@see BeforeTest} hook runs once:
     * if lifecycle methods had leaked into the test set, {@see $setUpCalls} would exceed 1.
     */
    public function theOnlyTest(): void
    {
        Assert::same($this->setUpCalls, 1);
    }
}
