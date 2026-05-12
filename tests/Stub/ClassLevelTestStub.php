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
 * Stub for verifying that lifecycle methods are excluded from the test set
 * when {@see Test} is placed on the class.
 *
 * If the {@see \Testo\Lifecycle\Internal\LifecycleLocatorInterceptor} works
 * correctly, only {@see realTest} ends up as a test; the four lifecycle
 * methods are still invoked as hooks around it but are not themselves tests.
 */
#[Test]
final class ClassLevelTestStub
{
    public static int $beforeTestCalls = 0;
    public static int $afterTestCalls = 0;
    public static int $beforeClassCalls = 0;
    public static int $afterClassCalls = 0;

    #[BeforeClass]
    public static function setUpClass(): void
    {
        ++self::$beforeClassCalls;
    }

    #[AfterClass]
    public static function tearDownClass(): void
    {
        ++self::$afterClassCalls;
    }

    #[BeforeTest]
    public function setUp(): void
    {
        ++self::$beforeTestCalls;
    }

    #[AfterTest]
    public function tearDown(): void
    {
        ++self::$afterTestCalls;
    }

    public function realTest(): void
    {
        Assert::same(self::$beforeTestCalls, 1);
        Assert::same(self::$beforeClassCalls, 1);
    }
}
