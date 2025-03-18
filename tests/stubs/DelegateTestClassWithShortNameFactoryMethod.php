<?php declare(strict_types=1);

namespace spriebsch\diContainer;

class DelegateTestClassWithShortNameFactoryMethod
{
    public function __construct(public readonly string $scalarParameter) {}
}
