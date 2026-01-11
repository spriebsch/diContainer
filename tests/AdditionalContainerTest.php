<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Exception;

#[CoversClass(DIContainer::class)]
#[CoversClass(AbstractFactory::class)]
#[CoversClass(Type::class)]
#[CoversClass(ContainerException::class)]
#[CoversClass(AutoWireException::class)]
class AdditionalContainerTest extends TestCase
{
    public function test_exception_when_no_factory_classes_provided(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('No factory classes provided');

        new DIContainer(new TestConfiguration());
    }

    public function test_exception_when_factory_method_throws_exception(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Exception "Regular method exception" while creating throwingMethod');

        $container->get('throwingMethod');
    }

    public function test_exception_when_virtual_type_factory_method_throws_exception(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Exception "Virtual type exception" while creating throwingVirtualType');

        $container->get('throwingVirtualType');
    }

    public function test_exception_when_virtual_type_factory_method_throws_error(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Error');

        $container->get('throwingErrorVirtualType');
    }

    public function test_exception_when_factory_method_does_not_return_object(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Factory method nonObjectMethod does not return object but string');

        $container->get('nonObjectMethod');
    }

    public function test_variadic_factory_method(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $instance = $container->get('variadicMethod', 'a', 'b', 'c');

        $this->assertInstanceOf(stdClass::class, $instance);
    }

    public function test_serialize_type_with_object_parameter(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);
        $object = new stdClass();

        // Use variadicMethod which accepts anything
        $instance1 = $container->get('variadicMethod', $object);
        $instance2 = $container->get('variadicMethod', $object);

        $this->assertSame($instance1, $instance2);
    }

    public function test_autowire_exception_cannot_create(): void
    {
        // AutoWireException::cannotCreate is defined but not used in AbstractFactory.
        // We can at least test that the method works as expected.
        $exception = new Exception('original', 123);
        $reflection = new \ReflectionClass(TestClassWithScalarConstructorParameters::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];

        $autoWireException = AutoWireException::cannotCreate('SomeClass', $parameter, $exception);

        $this->assertSame('Cannot auto-wire SomeClass: constructor parameter string: original', $autoWireException->getMessage());
        $this->assertSame(123, $autoWireException->getCode());
        $this->assertSame($exception, $autoWireException->getPrevious());
    }

    public function test_abstract_factory_getters(): void
    {
        $config = new TestConfiguration();
        $container = new DIContainer($config, TestFactory::class);

        $factory = $container->factory;

        $this->assertSame($config, $factory->Configuration());
        $this->assertSame($container, $factory->Container());
    }

    public function test_variadic_regular_factory_method(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        // TestClassWithoutConstructor matches the short name factory method in TestFactoryWithErrors
        $instance = $container->get(TestClassWithoutConstructor::class, 'a', 'b');

        $this->assertInstanceOf(TestClassWithoutConstructor::class, $instance);
    }

    public function test_exception_when_too_few_arguments_passed_to_regular_factory_method(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Type spriebsch\diContainer\TestClassWithScalarConstructorParametersAndShortMethod has 0 parameter(s), method TestClassWithScalarConstructorParametersAndShortMethod expects 3');

        $container->get(TestClassWithScalarConstructorParametersAndShortMethod::class);
    }

    public function test_autowire_no_constructor(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);
        $instance = $container->get(TestClassWithoutConstructor::class);
        $this->assertInstanceOf(TestClassWithoutConstructor::class, $instance);
    }

    public function test_autowire_empty_constructor(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);
        $instance = $container->get(TestClassWithoutConstructorParameters::class);
        $this->assertInstanceOf(TestClassWithoutConstructorParameters::class, $instance);
    }

    public function test_exception_when_type_does_not_exist(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Factory method for virtual type DoesNotExist does not exist');

        $container->get('DoesNotExist');
    }

    public function test_container_exception_type_does_not_exist(): void
    {
        $exception = ContainerException::typeDoesNotExist('DoesNotExist');
        $this->assertSame('Type (class or interface) DoesNotExist does not exist', $exception->getMessage());
    }
}
