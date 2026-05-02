<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

/**
 * Self-tests for multiple Before/After methods on a single class.
 */
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

    #[Test]
    public function multipleBeforeMethodsAreCalled(): void
    {
        // Both before methods should have been called
        Assert::true(\in_array('before-1', $this->log, true));
        Assert::true(\in_array('before-2', $this->log, true));
    }
}
