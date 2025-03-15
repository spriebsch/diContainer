<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithScalarConstructorParameters
{
    public function __construct(string $string, int $int, array $array) {}
}
