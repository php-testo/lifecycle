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
 * Self-tests for {@see BeforeTest} and {@see AfterTest} lifecycle attributes.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(AfterTest::class)]
#[Covers(LifecycleInterceptor::class)]
final class BeforeAfterTest
{
    /** @var list<string> */
    private array $executionLog = [];

    #[BeforeTest]
    public function setupFirst(): void
    {
        $this->executionLog[] = 'before';
    }

    #[AfterTest]
    public function teardownFirst(): void
    {
        $this->executionLog[] = 'after';
    }

    /**
     * The {@see BeforeTest} hook runs before the test body, so the log already
     * carries its entry by the time the test executes.
     */
    public function beforeTestRunsBeforeTestBody(): void
    {
        Assert::true(\in_array('before', $this->executionLog, true));
    }
}
