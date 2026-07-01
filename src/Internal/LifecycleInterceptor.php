<?php

declare(strict_types=1);

namespace Testo\Lifecycle\Internal;

use Testo\Common\Reflection;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Definition\CaseDefinition;
use Testo\Core\Definition\CaseDefinitions;
use Testo\Core\Value\CaseInstance;
use Testo\Core\Value\TestType;
use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\CaseLocatorInterceptor;
use Testo\Pipeline\Middleware\TestCaseRunInterceptor;
use Testo\Pipeline\Middleware\TestRunInterceptor;
use Testo\Tokenizer\DefinitionLocator;
use Testo\Tokenizer\Reflection\FileDefinitions;
use Testo\Tokenizer\Reflection\TokenizedFile;

/**
 * Wires lifecycle attributes ({@see BeforeClass}, {@see BeforeTest}, {@see AfterTest},
 * {@see AfterClass}) into Testo's pipeline:
 *
 * - As a {@see CaseLocatorInterceptor}, it removes lifecycle-annotated methods/functions from
 *   the discovered test set so they are not treated as tests by the test plugin.
 * - As a {@see TestCaseRunInterceptor}/{@see TestRunInterceptor}, it executes those
 *   methods/functions as lifecycle hooks around each test/case.
 *
 * Both class-based cases (methods of a {@see \Testo\Test} class) and function-based cases (a file of
 * top-level functions, whose {@see CaseDefinition::$reflection} is null) are supported. For a
 * function-based case the hooks are the file's lifecycle-annotated functions, discovered from the
 * source file of the case's tests.
 *
 * @internal
 * @psalm-internal Testo\Lifecycle
 */
#[InterceptorOptions(
    order: PHP_INT_MAX,
    testType: TestType::Test,
)]
final readonly class LifecycleInterceptor implements
    CaseLocatorInterceptor,
    TestRunInterceptor,
    TestCaseRunInterceptor
{
    #[\Override]
    public function locateTestCases(FileDefinitions $file, callable $next): CaseDefinitions
    {
        /** @var CaseDefinitions $result */
        $result = $next($file);

        foreach ($result->getCases() as $case) {
            foreach ($case->tests->getTests() as $name => $test) {
                $method = $test->reflection;
                if (Reflection::fetchFunctionAttributes(
                    $method,
                    attributeClass: LifecycleAttribute::class,
                    flags: \ReflectionAttribute::IS_INSTANCEOF,
                ) !== []) {
                    $case->tests->undefine($name);
                }
            }
        }

        return $result;
    }

    /**
     * Collect all the lifecycle hooks and cache them for execution during test runs.
     */
    #[\Override]
    public function runTestCase(CaseInfo $info, callable $next): CaseResult
    {
        # Lifecycle hooks are the class methods of a class-based case, or the lifecycle-annotated
        # free functions of a function-based case (no class reflection).
        $hooks = $info->definition->reflection !== null
            ? Reflection::findMethodsWithAttribute(
                $info->definition->reflection,
                LifecycleAttribute::class,
                flags: \ReflectionAttribute::IS_INSTANCEOF,
            )
            : self::collectCaseFunctions($info->definition);

        $result = self::group($hooks);

        # Execute BeforeClass hooks
        foreach ($result[BeforeClass::class] ?? [] as $hook) {
            self::execute($info->instance, $hook);
        }

        try {
            return $next($info->withAttribute(self::class, $result));
        } finally {
            # Execute AfterClass hooks
            foreach ($result[AfterClass::class] ?? [] as $hook) {
                self::execute($info->instance, $hook);
            }
        }
    }

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var array<class-string<LifecycleAttribute>, non-empty-list<\ReflectionFunctionAbstract>> $hooks */
        $hooks = $info->caseInfo->getAttribute(self::class, []);
        if ($hooks === []) {
            return $next($info);
        }

        foreach ($hooks[BeforeTest::class] ?? [] as $hook) {
            self::execute($info->caseInfo->instance, $hook);
        }

        try {
            return $next($info);
        } finally {
            foreach ($hooks[AfterTest::class] ?? [] as $hook) {
                self::execute($info->caseInfo->instance, $hook);
            }
        }
    }

    /**
     * Group lifecycle hooks by their attribute class, ordered within each group by priority
     * descending (higher priority runs first).
     *
     * @param iterable<\ReflectionFunctionAbstract> $hooks
     * @return array<class-string<LifecycleAttribute>, non-empty-list<\ReflectionFunctionAbstract>>
     */
    private static function group(iterable $hooks): array
    {
        /** @var array<class-string<LifecycleAttribute>, non-empty-array<int, non-empty-list<\ReflectionFunctionAbstract>>> $groups */
        $groups = [];
        foreach ($hooks as $hook) {
            /** @var \ReflectionAttribute<LifecycleAttribute>[] $attributes */
            $attributes = Reflection::fetchFunctionAttributes(
                $hook,
                attributeClass: LifecycleAttribute::class,
                flags: \ReflectionAttribute::IS_INSTANCEOF,
            );

            foreach ($attributes as $attribute) {
                $attr = $attribute->newInstance();
                /** @var int $priority */
                $priority = $attr->priority ?? 0;
                $groups[$attr::class][$priority][] = $hook;
            }
        }

        $result = [];
        foreach ($groups as $class => $hooksByPriority) {
            \krsort($hooksByPriority);
            $result[$class] = \array_merge(...$hooksByPriority);
        }

        return $result;
    }

    /**
     * Collect the lifecycle-annotated free functions of a function-based case.
     *
     * A function-based case carries no class reflection, so the hooks are located from the source
     * file of the case's tests: every function declared there is reflected and filtered down to
     * those carrying a lifecycle attribute.
     *
     * @return list<\ReflectionFunction>
     */
    private static function collectCaseFunctions(CaseDefinition $definition): array
    {
        $path = null;
        foreach ($definition->tests->getTests() as $test) {
            if ($test->reflection instanceof \ReflectionFunction) {
                $path = $test->reflection->getFileName();
                break;
            }
        }

        if (!\is_string($path)) {
            return [];
        }

        $file = new TokenizedFile(file: new \SplFileInfo($path), path: $path);

        return \array_values(\array_filter(
            DefinitionLocator::getFunctions($file),
            static fn(\ReflectionFunction $function): bool => Reflection::fetchFunctionAttributes(
                $function,
                attributeClass: LifecycleAttribute::class,
                flags: \ReflectionAttribute::IS_INSTANCEOF,
            ) !== [],
        ));
    }

    private static function execute(?CaseInstance $instance, \ReflectionFunctionAbstract $reflection): void
    {
        if ($reflection instanceof \ReflectionMethod) {
            $reflection->invoke($reflection->isStatic() ? null : $instance?->getInstance());
            return;
        }

        \assert($reflection instanceof \ReflectionFunction);
        $reflection->invoke();
    }
}
