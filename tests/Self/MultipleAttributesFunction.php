<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Lifecycle\BeforeTest;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;

/**
 * Self-test for several {@see BeforeTest} hooks declared as separate top-level functions of one
 * function-based case: every one of them must run before the test body.
 */
#[BeforeTest]
function firstBeforeFn(): void
{
    MultipleAttributesFunctionState::$log[] = 'before-1';
}

#[BeforeTest]
function secondBeforeFn(): void
{
    MultipleAttributesFunctionState::$log[] = 'before-2';
}

/**
 * Every {@see BeforeTest} function declared in the file runs before the test body.
 */
#[Test]
#[Covers(BeforeTest::class)]
#[Covers(LifecycleInterceptor::class)]
function allBeforeFunctionsAreCalled(): void
{
    Assert::true(\in_array('before-1', MultipleAttributesFunctionState::$log, true));
    Assert::true(\in_array('before-2', MultipleAttributesFunctionState::$log, true));
}

final class MultipleAttributesFunctionState
{
    /** @var list<string> */
    public static array $log = [];
}
