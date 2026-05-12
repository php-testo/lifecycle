<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Unit\Fixture;

use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;

final class ClassWithMultipleLifecycleOnOneMethod
{
    #[BeforeTest]
    #[AfterTest]
    public function both(): void {}

    public function realTest(): void {}
}
