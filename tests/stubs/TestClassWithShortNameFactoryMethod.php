<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class TestClassWithShortNameFactoryMethod
{
    public function __construct(public readonly string $scalarParameter) {}
}
