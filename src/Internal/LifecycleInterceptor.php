<?php

declare(strict_types=1);

namespace Testo\Lifecycle\Internal;

use Testo\Common\Reflection;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\CaseInstance;
use Testo\Core\Value\TestType;
use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestCaseRunInterceptor;
use Testo\Pipeline\Middleware\TestRunInterceptor;

/**
 * Processes lifecycle methods like {@see \Testo\Lifecycle\BeforeTest} and {@see \Testo\Lifecycle\AfterTest}.
 *
 * @internal
 * @psalm-internal Testo\Lifecycle
 */
#[InterceptorOptions(
    order: PHP_INT_MAX,
    testType: TestType::Test,
)]
final readonly class LifecycleInterceptor implements TestRunInterceptor, TestCaseRunInterceptor
{
    /**
     * Collect all the lifecycle methods and cache them for execution during test runs.
     */
    #[\Override]
    public function runTestCase(CaseInfo $info, callable $next): CaseResult
    {
        # Skip if there is no class
        if ($info->definition->reflection === null) {
            return $next($info);
        }

        # Get all lifecycle methods
        $all = Reflection::findMethodsWithAttribute(
            $info->definition->reflection,
            LifecycleAttribute::class,
            flags: \ReflectionAttribute::IS_INSTANCEOF,
        );


        # Group and sort
        /** @var array<class-string<LifecycleAttribute>, non-empty-array<int, non-empty-list<\ReflectionMethod>>> $groups */
        $result = $groups = [];
        foreach ($all as $method) {
            /** @var \ReflectionAttribute<LifecycleAttribute>[] $attributes */
            $attributes = Reflection::fetchFunctionAttributes(
                $method,
                attributeClass: LifecycleAttribute::class,
                flags: \ReflectionAttribute::IS_INSTANCEOF,
            );

            # Group by attribute class and priority
            foreach ($attributes as $attribute) {
                $attr = $attribute->newInstance();
                /** @var int $priority */
                $priority = $attr->priority ?? 0;
                $groups[$attr::class][$priority][] = $method;
            }
        }

        # Sort by priority descending
        foreach ($groups as $class => &$methodsByPriority) {
            \krsort($methodsByPriority);
            $result[$class] = \array_merge(...$methodsByPriority);
        }

        # Execute BeforeClass methods
        foreach ($result[BeforeClass::class] ?? [] as $method) {
            self::execute($info->instance, $method);
        }

        try {
            return $next($info->withAttribute(self::class, $result));
        } finally {
            # Execute AfterClass methods
            foreach ($result[AfterClass::class] ?? [] as $method) {
                self::execute($info->instance, $method);
            }
        }
    }

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var array<class-string<LifecycleAttribute>, non-empty-list<\ReflectionMethod>> $methods */
        $methods = $info->caseInfo->getAttribute(self::class);
        if ($methods === []) {
            return $next($info);
        }

        foreach ($methods[BeforeTest::class] ?? [] as $method) {
            self::execute($info->caseInfo->instance, $method);
        }

        try {
            return $next($info);
        } finally {
            foreach ($methods[AfterTest::class] ?? [] as $method) {
                self::execute($info->caseInfo->instance, $method);
            }
        }
    }

    private static function execute(?CaseInstance $instance, \ReflectionMethod $reflection): void
    {
        $reflection->invoke($reflection->isStatic() ? null : $instance?->getInstance());
    }
}
