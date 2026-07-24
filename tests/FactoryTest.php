<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractFactory::class)]
#[CoversClass(Type::class)]
#[CoversClass(ContainerException::class)]
#[CoversClass(DIContainer::class)]
#[CoversClass(AutoWireException::class)]
final class FactoryTest extends TestCase
{
    public function test_inherited_constructor_has_one_parameter(): void
    {
        $reflectionClass = new \ReflectionClass(ChildClassInheritingConstructor::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertSame(1, count($constructor->getParameters()));
    }

    public function test_exception_when_type_is_not_virtual_but_does_not_exist(): void
    {
        $configuration = new TestConfiguration();
        $container = new DIContainer($configuration, TestFactory::class);

        $factory = new TestFactory($configuration, $container);

        $type = new readonly class('SomeClass') extends Type {
            public function isVirtual(): bool
            {
                return false;
            }

            public function exists(): bool
            {
                return false;
            }
        };

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Type (class or interface) SomeClass does not exist');

        $factory->create($type);
    }
}
