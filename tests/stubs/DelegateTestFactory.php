<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use stdClass;

readonly class DelegateTestFactory extends AbstractFactory
{
    public function delegateVirtualType(): object
    {
        return new stdClass;
    }

    public function DelegateTestClassWithShortNameFactoryMethod(string $parameter): DelegateTestClassWithShortNameFactoryMethod
    {
        return new DelegateTestClassWithShortNameFactoryMethod($parameter);
    }

    public function spriebsch_diContainer_DelegateTestClassWithLongNameFactoryMethod(string $parameter): DelegateTestClassWithLongNameFactoryMethod
    {
        return new DelegateTestClassWithLongNameFactoryMethod($parameter);
    }
}
