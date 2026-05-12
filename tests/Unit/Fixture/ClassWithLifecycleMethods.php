<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Unit\Fixture;

use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;

final class ClassWithLifecycleMethods
{
    public function plainTest(): void {}

    #[BeforeTest]
    public function setUp(): void {}

    #[AfterTest]
    public function tearDown(): void {}

    #[BeforeClass]
    public static function setUpClass(): void {}

    #[AfterClass]
    public static function tearDownClass(): void {}

    public function anotherPlainTest(): void {}
}
