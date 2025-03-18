<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use TestClassWithoutNamespace;

#[CoversClass(DIContainer::class)]
#[CoversClass(AbstractFactory::class)]
#[CoversClass(Type::class)]
#[CoversClass(ContainerException::class)]
#[CoversClass(AutoWireException::class)]
class ContainerTest extends TestCase
{
    public function test_exception_when_factory_class_does_not_exist(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Factory class does-not-exist does not exist');

        new DIContainer(new TestConfiguration, 'does-not-exist');
    }

    public function test_exception_when_factory_class_does_not_extend_abstract_factory(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('is no instance of');

        new DIContainer(
            new TestConfiguration,
            TestFactoryThatDoesNotExtendAbstractFactory::class,
        );
    }

    public function test_exception_when_trying_to_auto_wire_scalar_parameters(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('has scalar type');

        $container->get(TestClassWithScalarConstructorParameters::class);
    }

    public function test_exception_when_virtual_type_does_not_exist(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Factory method for virtual type does-not-exist does not exist');

        $container->get('does-not-exist');
    }

    public function test_exception_when_type_does_not_exist(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        // $this->expectExceptionMessage('class\\DoesNotExist does not exist');

        $container->get('class\\DoesNotExist');
    }

    public function test_exception_when_dependency_has_untyped_constructor_parameter(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('constructor parameter untypedParameter');

        $container->get(TestClassWithDependencyThatHasUntypedConstructorParameter::class);
    }

    public function test_exception_when_custom_method_returns_no_object(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('does not return object but');

        $container->get('TypeThatDoesNotReturnObject');
    }

    public function test_creates_non_namespaced_class(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $instance = $container->get(TestClassWithoutNamespace::class);

        $this->assertInstanceOf(TestClassWithoutNamespace::class, $instance);
    }

    public function test_creates_class_without_constructor(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $instance = $container->get(TestClassWithoutConstructor::class);

        $this->assertInstanceOf(TestClassWithoutConstructor::class, $instance);
    }

    public function test_creates_class_without_constructor_parameters(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithoutConstructorParameters::class,
            $container->get(TestClassWithoutConstructorParameters::class),
        );
    }

    public function test_creates_class_with_dependencies(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithDependency::class,
            $container->get(TestClassWithDependency::class),
        );
    }

    public function test_creates_class_with_configuration_dependency(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithConfigurationDependency::class,
            $container->get(TestClassWithConfigurationDependency::class),
        );
    }

    public function test_creates_class_with_container_dependency(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithContainerDependency::class,
            $container->get(TestClassWithContainerDependency::class),
        );
    }

    public function test_creates_type_with_short_name_method(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithShortNameFactoryMethod::class,
            $container->get(TestClassWithShortNameFactoryMethod::class, 'the-value'),
        );
    }

    public function test_passes_type_argument_to_constructor(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithScalarConstructorParametersAndShortMethod::class,
            $container->get(
                TestClassWithScalarConstructorParametersAndShortMethod::class,
                'the-string',
                42,
                [],
            ),
        );
    }

    public function test_exception_when_passing_arguments_to_constructor_without_arguments(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Type spriebsch\diContainer\TestClassWithoutConstructorParametersAndShortMethod has 1 parameter(s)',
        );

        $container->get(
            TestClassWithoutConstructorParametersAndShortMethod::class,
            'method-does-not-have-parameters',
        );
    }

    public function test_exception_when_passing_not_enough_arguments(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            'Type spriebsch\diContainer\TestClassWithScalarConstructorParametersAndShortMethod has 1 parameter(s)',
        );

        $container->get(
            TestClassWithScalarConstructorParametersAndShortMethod::class,
            'just-one-argument',
        );
    }

    public function test_passes_virtual_type_argument_to_constructor(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $instance = $container->get(
            'virtualTypeWithParameter',
            'the-string',
        );

        $this->assertInstanceOf(
            TestClassVirtualTypeWithParameter::class,
            $instance,
        );
    }

    public function test_creates_type_with_long_name_methods(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithLongNameFactoryMethods::class,
            $container->get(TestClassWithLongNameFactoryMethods::class),
        );
    }

    public function test_creates_virtual_type(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            stdClass::class,
            $container->get('virtualType'),
        );
    }

    public function test_creates_virtual_type_with_parameters(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassVirtualTypeWithParameter::class,
            $container->get('virtualTypeWithParameter', 'the-parameter'),
        );
    }

    public function test_manages_single_instance_on_types(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertSame(
            $container->get(TestClassWithDependency::class),
            $container->get(TestClassWithDependency::class),
        );
    }

    public function test_passes_parameter_to_virtual_type_factory_method(): void
    {
        $parameter = 'the-parameter';
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $object = $container->get('virtualTypeWithParameter', $parameter);

        $this->assertSame($parameter, $object->parameter);
    }

    public function test_manages_single_instance_on_virtual_types(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertSame(
            $container->get('virtualTypeWithParameter', 'the-parameter'),
            $container->get('virtualTypeWithParameter', 'the-parameter'),
        );
    }

    public function test_creates_new_instance_of_virtual_types_for_different_parameters(): void
    {
        $container = new DIContainer(new TestConfiguration, TestFactory::class);

        $this->assertNotSame(
            $container->get('virtualTypeWithParameter', 'the-parameter'),
            $container->get('virtualTypeWithParameter', 'other-parameter'),
        );
    }

    public function test_factory_can_be_cascaded(): void
    {
        $container = new DIContainer(
            new TestConfiguration,
            TestFactory::class,
            DelegateTestFactory::class,
        );

        $this->assertInstanceOf(
            stdClass::class,
            $container->get('delegateVirtualType'),
        );

        $this->assertInstanceOf(
            DelegateTestClassWithLongNameFactoryMethod::class,
            $container->get(DelegateTestClassWithLongNameFactoryMethod::class),
        );

        $this->assertInstanceOf(
            DelegateTestClassWithShortNameFactoryMethod::class,
            $container->get(DelegateTestClassWithShortNameFactoryMethod::class),
        );
    }
}
