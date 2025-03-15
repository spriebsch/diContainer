<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithMultipleDependecies
{
    public function __construct(
        TestDependency             $testDependency,
        TestDependencyOfDependency $dependencyOfDependency,
    ) {}
}
