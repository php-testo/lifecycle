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

/**
 * End-to-end counterpart of {@see LifecycleLocatorFeatureTest} for a function-based test case.
 *
 * Runs the whole pipeline against {@see \Tests\Lifecycle\Stub\FunctionLevelTestStub} and verifies
 * that the lifecycle functions are invoked as hooks around the single test function (so the test,
 * which asserts the hooks fired, passes), while the lifecycle functions are not themselves tests.
 */
#[Test]
#[Covers(LifecycleInterceptor::class)]
#[TestingSuite(path: __DIR__ . '/../Stub/FunctionLevelTestStub.php')]
final class LifecycleLocatorFunctionFeatureTest
{
    public function __construct()
    {
        # Functions are not autoloadable: load the stub so TestRunner::runTest() can resolve the
        # function names below. The pipeline re-includes the same file (include_once) when it runs.
        require_once __DIR__ . '/../Stub/FunctionLevelTestStub.php';
    }

    public function realTestFunctionIsDiscoveredAndPasses(): void
    {
        $result = TestRunner::runTest('Tests\Lifecycle\Stub\stubRealTest');

        Assert::same($result->status, Status::Passed);
    }

    public function setUpFunctionIsExcludedFromTestSet(): void
    {
        $this->assertFunctionIsNotInTestSet('Tests\Lifecycle\Stub\stubSetUp');
    }

    public function tearDownFunctionIsExcludedFromTestSet(): void
    {
        $this->assertFunctionIsNotInTestSet('Tests\Lifecycle\Stub\stubTearDown');
    }

    public function setUpClassFunctionIsExcludedFromTestSet(): void
    {
        $this->assertFunctionIsNotInTestSet('Tests\Lifecycle\Stub\stubSetUpClass');
    }

    public function tearDownClassFunctionIsExcludedFromTestSet(): void
    {
        $this->assertFunctionIsNotInTestSet('Tests\Lifecycle\Stub\stubTearDownClass');
    }

    /**
     * {@see TestRunner::runTest()} throws when no matching test result is found; a lifecycle
     * function is never scheduled as a test, so the lookup must fail.
     */
    private function assertFunctionIsNotInTestSet(string $functionName): void
    {
        try {
            TestRunner::runTest($functionName);
        } catch (\Throwable) {
            Assert::true(true);
            return;
        }

        Assert::true(false, "Function `{$functionName}` should not be part of the test set but it ran.");
    }
}
