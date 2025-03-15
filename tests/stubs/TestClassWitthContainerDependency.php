<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithContainerDependency
{
    public function __construct(Container $container) {}
}
