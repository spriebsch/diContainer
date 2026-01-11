<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use Exception;
use stdClass;

readonly class TestFactoryWithErrors extends AbstractFactory
{
    public function throwingMethod(): object
    {
        throw new Exception('Regular method exception');
    }

    public function throwingVirtualType(): object
    {
        throw new Exception('Virtual type exception');
    }

    public function throwingErrorVirtualType(): object
    {
        throw new \Error('Error');
    }

    public function nonObjectMethod(): mixed
    {
        return 'not-an-object';
    }

    public function variadicMethod(...$args): stdClass
    {
        return new stdClass();
    }

    public function TestClassWithoutConstructor(string ...$args): TestClassWithoutConstructor
    {
        return new TestClassWithoutConstructor();
    }
}
