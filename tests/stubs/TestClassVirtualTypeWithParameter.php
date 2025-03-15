<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassVirtualTypeWithParameter
{
    public function __construct(public readonly string $parameter) {}
}
