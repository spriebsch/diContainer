<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use SQLite3;
use stdClass;

readonly class TestFactory extends AbstractFactory
{
    public function TypeThatDoesNotReturnObject()
    {
        return 'no-object';
    }

    public function TestClassWithScalarConstructorParametersAndShortMethod(
        string $string,
        int $int,
        array $array
    ): TestClassWithScalarConstructorParametersAndShortMethod
    {
        return new TestClassWithScalarConstructorParametersAndShortMethod(
            $string,
            $int,
            $array
        );
    }

    public function Sqlite(): SQLite3
    {
        return new SQLite3(':memory:');
    }

    public function virtualType(): object
    {
        return new stdClass;
    }

    public function virtualTypeWithParameter(string $parameter): object
    {
        return new TestClassVirtualTypeWithParameter($parameter);
    }

    public function TestClassWithShortNameFactoryMethod(): TestClassWithShortNameFactoryMethod
    {
        return new TestClassWithShortNameFactoryMethod('the-value');
    }

    public function TestClassWithoutConstructorParametersAndShortMethod(): TestClassWithoutConstructorParametersAndShortMethod
    {
        return new TestClassWithoutConstructorParametersAndShortMethod;
    }

    public function spriebsch_diContainer_TestClassWithLongNameFactoryMethods(): TestClassWithLongNameFactoryMethods
    {
        return new TestClassWithLongNameFactoryMethods('the-value');
    }

    public function spriebsch_TestClassWithLongNameFactoryMethods(): \spriebsch\TestClassWithLongNameFactoryMethods
    {
        return new \spriebsch\TestClassWithLongNameFactoryMethods('the-value');
    }
}
