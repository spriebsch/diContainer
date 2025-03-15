<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithConfigurationDependency
{
    public function __construct(Configuration $configuration) {}
}
