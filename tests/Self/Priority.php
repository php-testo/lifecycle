<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\BeforeTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-tests for lifecycle method priority ordering.
 *
 * Higher priority methods are executed first.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(LifecycleInterceptor::class)]
final class Priority
{
    /** @var list<string> */
    private array $beforeLog = [];

    #[BeforeTest(priority: 10)]
    public function highPriorityBefore(): void
    {
        $this->beforeLog[] = 'high';
    }

    #[BeforeTest(priority: 0)]
    public function defaultPriorityBefore(): void
    {
        $this->beforeLog[] = 'default';
    }

    #[BeforeTest(priority: -10)]
    public function lowPriorityBefore(): void
    {
        $this->beforeLog[] = 'low';
    }

    /**
     * Higher-priority {@see BeforeTest} methods run before lower-priority ones,
     * so the log is ordered high → default → low.
     */
    public function beforeMethodsRunInPriorityOrder(): void
    {
        $highIndex = \array_search('high', $this->beforeLog, true);
        $defaultIndex = \array_search('default', $this->beforeLog, true);
        $lowIndex = \array_search('low', $this->beforeLog, true);

        Assert::true($highIndex < $defaultIndex);
        Assert::true($defaultIndex < $lowIndex);
    }
}
