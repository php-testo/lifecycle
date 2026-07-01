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
 * Self-tests for multiple {@see BeforeTest}/{@see AfterTest} methods declared on a single class.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(AfterTest::class)]
#[Covers(LifecycleInterceptor::class)]
final class MultipleAttributes
{
    /** @var list<string> */
    private array $log = [];

    #[BeforeTest]
    public function firstBefore(): void
    {
        $this->log[] = 'before-1';
    }

    #[BeforeTest]
    public function secondBefore(): void
    {
        $this->log[] = 'before-2';
    }

    #[AfterTest]
    public function firstAfter(): void
    {
        $this->log[] = 'after-1';
    }

    #[AfterTest]
    public function secondAfter(): void
    {
        $this->log[] = 'after-2';
    }

    /**
     * Every {@see BeforeTest} method declared on the class runs before the test body.
     */
    public function allBeforeMethodsAreCalled(): void
    {
        Assert::true(\in_array('before-1', $this->log, true));
        Assert::true(\in_array('before-2', $this->log, true));
    }
}
