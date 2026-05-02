<?php

declare(strict_types=1);

namespace Testo\Lifecycle;

use Testo\Lifecycle\Internal\LifecycleAttribute;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;

/**
 * Marks a method to be executed once after all tests in the test case.
 *
 * Typically used for cleanup operations after all tests have completed.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
#[FallbackInterceptor(LifecycleInterceptor::class)]
final readonly class AfterClass implements Interceptable, LifecycleAttribute
{
    public function __construct(
        /**
         * The priority of the method.
         * Higher priority methods are executed first.
         */
        public int $priority = 0,
    ) {}
}
