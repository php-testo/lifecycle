<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * Self-tests for static lifecycle methods.
 */
final class StaticMethod
{
    public static bool $staticBeforeCalled = false;

    #[BeforeTest]
    public static function staticBefore(): void
    {
        self::$staticBeforeCalled = true;
    }

    #[Test]
    public function staticBeforeMethodIsCalled(): void
    {
        Assert::true(self::$staticBeforeCalled);
    }
}
