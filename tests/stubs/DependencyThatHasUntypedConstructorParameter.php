<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class DependencyThatHasUntypedConstructorParameter
{
    public function __construct($untypedParameter) {}
}
