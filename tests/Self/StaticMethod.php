<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\BeforeTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-tests for static lifecycle methods.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(LifecycleInterceptor::class)]
final class StaticMethod
{
    public static bool $staticBeforeCalled = false;

    #[BeforeTest]
    public static function staticBefore(): void
    {
        self::$staticBeforeCalled = true;
    }

    /**
     * A {@see BeforeTest} hook declared as a static method is invoked before the test body.
     */
    public function staticBeforeMethodIsCalled(): void
    {
        Assert::true(self::$staticBeforeCalled);
    }
}
