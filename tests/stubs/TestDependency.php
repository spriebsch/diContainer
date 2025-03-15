<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestDependency
{
    public function __construct(TestDependencyOfDependency $dependency) {}
}
