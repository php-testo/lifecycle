<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * Self-tests for lifecycle method priority ordering.
 *
 * Higher priority methods are executed first.
 */
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

    #[Test]
    public function beforeMethodsRunInPriorityOrder(): void
    {
        // Higher priority runs first
        // Find the indexes
        $highIndex = \array_search('high', $this->beforeLog, true);
        $defaultIndex = \array_search('default', $this->beforeLog, true);
        $lowIndex = \array_search('low', $this->beforeLog, true);

        // Verify order: high < default < low (by index)
        Assert::true($highIndex < $defaultIndex);
        Assert::true($defaultIndex < $lowIndex);
    }
}
