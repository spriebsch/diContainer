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
        $this->expectExceptionMessageIsOrContains('No factory classes provided');

        new DIContainer(new TestConfiguration());
    }

    public function test_exception_when_factory_method_throws_exception(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Exception "Regular method exception" while creating throwingMethod');

        /** @var class-string $type */
        $type = 'throwingMethod';
        $container->get($type);
    }

    public function test_exception_identifies_type_the_container_tried_to_create(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectExceptionMessage('Cannot create type throwingMethod: Exception "Regular method exception" while creating throwingMethod');

        /** @var class-string $type */
        $type = 'throwingMethod';
        $container->get($type);
    }

    public function test_exception_chains_factory_exception(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        try {
            /** @var class-string $type */
            $type = 'throwingMethod';
            $container->get($type);
        } catch (ContainerException $exception) {
            $this->assertSame('Exception "Regular method exception" while creating throwingMethod', $exception->getPrevious()?->getMessage());

            return;
        }

        $this->fail('Expected a ContainerException');
    }

    public function test_exception_when_virtual_type_factory_method_throws_exception(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Exception "Virtual type exception" while creating throwingVirtualType');

        /** @var class-string $type */
        $type = 'throwingVirtualType';
        $container->get($type);
    }

    public function test_exception_when_virtual_type_factory_method_throws_error(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Error');

        /** @var class-string $type */
        $type = 'throwingErrorVirtualType';
        $container->get($type);
    }

    public function test_exception_when_factory_method_does_not_return_object(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Factory method nonObjectMethod does not return object but string');

        /** @var class-string $type */
        $type = 'nonObjectMethod';
        $container->get($type);
    }

    public function test_variadic_factory_method(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);

        /** @var class-string $type */
        $type = 'variadicMethod';
        $instance = $container->get($type, 'a', 'b', 'c');

        $this->assertInstanceOf(stdClass::class, $instance);
    }

    public function test_serialize_type_with_object_parameter(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactoryWithErrors::class);
        $object = new stdClass();

        /** @var class-string $type */
        $type = 'variadicMethod';

        // Use variadicMethod which accepts anything
        $instance1 = $container->get($type, $object);
        $instance2 = $container->get($type, $object);

        $this->assertSame($instance1, $instance2);
    }

    public function test_autowire_exception_cannot_create(): void
    {
        // AutoWireException::cannotCreate is defined but not used in AbstractFactory.
        // We can at least test that the method works as expected.
        $exception = new Exception('original', 123);
        $reflection = new \ReflectionClass(TestClassWithScalarConstructorParameters::class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            $this->fail('Test class should have a constructor');
        }
        $parameter = $constructor->getParameters()[0];

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
        $this->expectExceptionMessageIsOrContains('Type spriebsch\diContainer\TestClassWithScalarConstructorParametersAndShortMethod has 0 parameter(s), method TestClassWithScalarConstructorParametersAndShortMethod expects 3');

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
        $this->expectExceptionMessageIsOrContains('Factory method for virtual type DoesNotExist does not exist');

        /** @var class-string $type */
        $type = 'DoesNotExist';
        $container->get($type);
    }

    public function test_container_exception_type_does_not_exist(): void
    {
        $exception = ContainerException::typeDoesNotExist('DoesNotExist');
        $this->assertSame('Type (class or interface) DoesNotExist does not exist', $exception->getMessage());
    }

    public function test_exception_when_method_expects_zero_parameters_but_parameters_given(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Type spriebsch\diContainer\TestClassWithoutConstructorParametersAndShortMethod has 1 parameter(s), method TestClassWithoutConstructorParametersAndShortMethod expects 0');

        $container->get(TestClassWithoutConstructorParametersAndShortMethod::class, 'unexpected');
    }

    public function test_exception_when_method_expects_more_parameters_than_given(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Type spriebsch\diContainer\TestClassWithScalarConstructorParametersAndShortMethod has 1 parameter(s), method TestClassWithScalarConstructorParametersAndShortMethod expects 3');

        $container->get(TestClassWithScalarConstructorParametersAndShortMethod::class, 'only-one');
    }

    public function test_autowire_exception_constructor_parameter_has_no_type(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Exception "Cannot auto-wire: constructor parameter untypedParameter of spriebsch\diContainer\DependencyThatHasUntypedConstructorParameter has no type" while creating spriebsch\diContainer\DependencyThatHasUntypedConstructorParameter');

        $container->get(DependencyThatHasUntypedConstructorParameter::class);
    }

    public function test_exception_when_method_expects_fewer_parameters_than_given(): void
    {
        $container = new DIContainer(new TestConfiguration(), TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Type spriebsch\diContainer\TestClassWithShortNameFactoryMethod has 2 parameter(s), method TestClassWithShortNameFactoryMethod expects 1');

        $container->get(TestClassWithShortNameFactoryMethod::class, 'param1', 'param2');
    }

    public function test_exception_when_factory_class_is_no_instance_of_abstract_factory(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Factory spriebsch\diContainer\TestFactoryThatDoesNotExtendAbstractFactory is no instance of spriebsch\diContainer\AbstractFactory');

        new DIContainer(new TestConfiguration(), TestFactoryThatDoesNotExtendAbstractFactory::class);
    }

    public function test_exception_when_factory_class_does_not_exist(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageIsOrContains('Factory class DoesNotExist does not exist');

        new DIContainer(new TestConfiguration(), 'DoesNotExist');
    }
}
