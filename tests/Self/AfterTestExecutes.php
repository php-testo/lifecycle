<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test proving the {@see AfterTest} hook actually runs.
 *
 * An "after" hook cannot be observed from inside the test it follows, so the proof is spread across
 * two tests: the first only triggers the hook, the second (declared later, hence run later) asserts
 * that the hook fired in between. State is kept in a static array so it survives between the two.
 */
#[Test]
#[Covers(AfterTest::class)]
#[Covers(LifecycleInterceptor::class)]
final class AfterTestExecutes
{
    /** @var list<string> */
    public static array $log = [];

    #[AfterTest]
    public function recordAfter(): void
    {
        self::$log[] = 'after';
    }

    /**
     * The first test does nothing but let its {@see AfterTest} hook run afterwards.
     */
    public function firstTestTriggersAfterHook(): void
    {
        Assert::true(true);
    }

    /**
     * By the time the second test runs, the first test's {@see AfterTest} hook has already fired.
     */
    public function secondTestSeesPriorAfterHook(): void
    {
        Assert::true(\in_array('after', self::$log, true));
    }
}
