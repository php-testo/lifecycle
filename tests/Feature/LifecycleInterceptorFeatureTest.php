<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Feature;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Lifecycle\Stub\ClassLevelTestStub;

/**
 * End-to-end checks that {@see \Testo\Lifecycle\Internal\LifecycleInterceptor}
 * removes methods carrying lifecycle attributes from the test set produced by the
 * test plugin, while leaving them in place as lifecycle hooks at run time.
 *
 * Each feature test runs the whole testo pipeline (file/case discovery + run) against
 * {@see ClassLevelTestStub} via {@see \Testo\Testing\Helper\TestRunner}, then inspects the result.
 */
#[Test]
#[Covers(LifecycleInterceptor::class)]
#[TestingSuite(path: __DIR__ . '/../Stub/ClassLevelTestStub.php')]
final class LifecycleLocatorFeatureTest
{
    public function realTestMethodIsDiscoveredAndPasses(): void
    {
        $result = TestRunner::runTest([ClassLevelTestStub::class, 'realTest']);

        Assert::same($result->status, Status::Passed);
    }

    public function setUpMethodIsExcludedFromTestSet(): void
    {
        $this->assertMethodIsNotInTestSet('setUp');
    }

    public function tearDownMethodIsExcludedFromTestSet(): void
    {
        $this->assertMethodIsNotInTestSet('tearDown');
    }

    public function setUpClassMethodIsExcludedFromTestSet(): void
    {
        $this->assertMethodIsNotInTestSet('setUpClass');
    }

    public function tearDownClassMethodIsExcludedFromTestSet(): void
    {
        $this->assertMethodIsNotInTestSet('tearDownClass');
    }

    /**
     * {@see \Testo\Testing\Helper\TestRunner::runTest()} throws when no matching test result is found.
     * (It tries to format the missing method into the exception message by
     * invoking it as a callable, which itself fails for non-static methods;
     * we therefore catch any {@see \Throwable} rather than only the intended
     * {@see \InvalidArgumentException}.)
     */
    private function assertMethodIsNotInTestSet(string $methodName): void
    {
        try {
            TestRunner::runTest([ClassLevelTestStub::class, $methodName]);
        } catch (\Throwable) {
            Assert::true(true);
            return;
        }

        Assert::true(false, "Method `{$methodName}` should be filtered out of the test set but it ran.");
    }
}
