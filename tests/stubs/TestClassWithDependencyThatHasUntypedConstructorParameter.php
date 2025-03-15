<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithDependencyThatHasUntypedConstructorParameter
{
    public function __construct(DependencyThatHasUntypedConstructorParameter $dependency) {}
}
