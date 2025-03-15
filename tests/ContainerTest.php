<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Container::class)]
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

        new Container(new TestConfiguration, 'does-not-exist');
    }

    public function test_exception_when_factory_class_does_not_extend_abstract_factory(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('is no instance of');

        new Container(
            new TestConfiguration,
            TestFactoryThatDoesNotExtendAbstractFactory::class,
        );
    }

    public function test_creates_class_without_constructor(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $instance = $container->get(TestClassWithoutConstructor::class);

        $this->assertInstanceOf(TestClassWithoutConstructor::class, $instance);
    }

    public function test_creates_class_without_constructor_parameters(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithoutConstructorParameters::class,
            $container->get(TestClassWithoutConstructorParameters::class),
        );
    }

    public function test_manages_single_instance(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->assertSame(
            $container->get(TestClassWithDependency::class),
            $container->get(TestClassWithDependency::class),
        );
    }

    public function test_exception_when_trying_to_auto_wire_scalar_parameters(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('has scalar type');

        $container->get(TestClassWithScalarConstructorParameters::class);
    }

    public function test_exception_when_type_does_not_exist(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Virtual type does-not-exist does not exist');

        $container->get('does-not-exist');
    }

    public function test_exception_when_custom_method_returns_no_object(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('does not return object but');

        $container->get('TypeThatDoesNotReturnObject');
    }

    public function test_creates_type_with_short_name_method(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithShortNameFactoryMethod::class,
            $container->get(TestClassWithShortNameFactoryMethod::class),
        );
    }

    public function test_creates_type_with_long_name_methods(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            TestClassWithLongNameFactoryMethods::class,
            $container->get(TestClassWithLongNameFactoryMethods::class),
        );
    }

    public function test_creates_virtual_type_as_string(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            stdClass::class,
            $container->get('virtualType'),
        );
    }

    public function test_creates_virtual_type(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->assertInstanceOf(
            stdClass::class,
            $container->get(new Type('virtualType')),
        );
    }

    public function test_exception_when_dependency_has_untyped_constructor_parameter(): void
    {
        $container = new Container(new TestConfiguration, TestFactory::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('constructor parameter untypedParameter');

        $container->get(TestClassWithDependencyThatHasUntypedConstructorParameter::class);
    }
}
