<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Unit\Internal;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\TestType;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;
use Testo\Tokenizer\DefinitionLocator;
use Testo\Tokenizer\Reflection\FileDefinitions;
use Testo\Tokenizer\Reflection\TokenizedFile;
use Tests\Lifecycle\Unit\Fixture\ClassWithLifecycleMethods;
use Tests\Lifecycle\Unit\Fixture\ClassWithMultipleLifecycleOnOneMethod;
use Tests\Lifecycle\Unit\Fixture\ClassWithoutLifecycle;

#[Test]
#[Covers(LifecycleInterceptor::class)]
final class LifecycleInterceptorTest
{
    private string $fixturesDir = __DIR__ . '/../Fixture/';
    private LifecycleInterceptor $interceptor;

    public function __construct()
    {
        $this->interceptor = new LifecycleInterceptor();
    }

    /**
     * When the test plugin has registered every public void method of a class
     * (because the class carries the {@see Test} attribute), the lifecycle
     * locator must strip out methods marked with lifecycle attributes.
     */
    public function removesLifecycleMethodsFromTestSet(): void
    {
        $definition = $this->makeDefinitionWithAllPublicMethodsAsTests(
            'ClassWithLifecycleMethods.php',
            ClassWithLifecycleMethods::class,
        );

        # Sanity: pre-filter, all six public methods are present
        Assert::array($definition->cases->getCases()[0]->tests->getTests())
            ->hasCount(6)
            ->hasKeys('plainTest', 'anotherPlainTest', 'setUp', 'tearDown', 'setUpClass', 'tearDownClass');

        $result = $this->interceptor->locateTestCases(
            $definition,
            static fn(FileDefinitions $f) => $f->cases,
        );

        $tests = $result->getCases()[0]->tests->getTests();

        Assert::array($tests)
            ->hasCount(2)
            ->hasKeys('plainTest', 'anotherPlainTest')
            ->doesNotHaveKeys('setUp', 'tearDown', 'setUpClass', 'tearDownClass');
    }

    /**
     * Classes without lifecycle attributes must pass through untouched.
     */
    public function passesThroughWhenNoLifecycleMethodsPresent(): void
    {
        $definition = $this->makeDefinitionWithAllPublicMethodsAsTests(
            'ClassWithoutLifecycle.php',
            ClassWithoutLifecycle::class,
        );

        $result = $this->interceptor->locateTestCases(
            $definition,
            static fn(FileDefinitions $f) => $f->cases,
        );

        Assert::array($result->getCases()[0]->tests->getTests())
            ->hasCount(2)
            ->hasKeys('alpha', 'beta');
    }

    /**
     * A method carrying several lifecycle attributes at once must still be removed exactly once.
     */
    public function removesMethodCarryingMultipleLifecycleAttributes(): void
    {
        $definition = $this->makeDefinitionWithAllPublicMethodsAsTests(
            'ClassWithMultipleLifecycleOnOneMethod.php',
            ClassWithMultipleLifecycleOnOneMethod::class,
        );

        $result = $this->interceptor->locateTestCases(
            $definition,
            static fn(FileDefinitions $f) => $f->cases,
        );

        Assert::array($result->getCases()[0]->tests->getTests())
            ->hasCount(1)
            ->hasKeys('realTest')
            ->doesNotHaveKeys('both');
    }

    /**
     * The interceptor must delegate to {@see $next} and return its result.
     */
    public function returnsValueReturnedByNext(): void
    {
        $definition = $this->makeDefinitionWithAllPublicMethodsAsTests(
            'ClassWithoutLifecycle.php',
            ClassWithoutLifecycle::class,
        );

        $nextCalled = false;
        $result = $this->interceptor->locateTestCases(
            $definition,
            static function (FileDefinitions $f) use (&$nextCalled): \Testo\Core\Definition\CaseDefinitions {
                $nextCalled = true;
                return $f->cases;
            },
        );

        Assert::true($nextCalled);
        Assert::same($result, $definition->cases);
    }

    private function makeDefinitionWithAllPublicMethodsAsTests(string $fixture, string $classFqn): FileDefinitions
    {
        $path = $this->fixturesDir . $fixture;
        $file = new TokenizedFile(file: new \SplFileInfo($path), path: $path);
        $definition = new FileDefinitions($file, classes: DefinitionLocator::getClasses($file));

        $reflection = $definition->classes[$classFqn] ?? throw new \RuntimeException(
            "Fixture class {$classFqn} not found in {$path}",
        );

        $case = $definition->cases->define($reflection, $definition, type: TestType::Test);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $case->tests->define($method);
        }

        return $definition;
    }
}
