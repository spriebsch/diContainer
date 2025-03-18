<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use stdClass;

readonly class DelegateTestFactory extends AbstractFactory
{
    public function delegateVirtualType(): object
    {
        return new stdClass;
    }

    public function DelegateTestClassWithShortNameFactoryMethod(): DelegateTestClassWithShortNameFactoryMethod
    {
        return new DelegateTestClassWithShortNameFactoryMethod('the-value');
    }

    public function spriebsch_diContainer_DelegateTestClassWithLongNameFactoryMethod(): DelegateTestClassWithLongNameFactoryMethod
    {
        return new DelegateTestClassWithLongNameFactoryMethod('the-value');
    }
}
