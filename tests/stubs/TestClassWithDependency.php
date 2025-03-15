<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithDependency
{
    public function __construct(TestDependency $dependency) {}
}
