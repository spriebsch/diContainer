# DI Container

A lightweight, smart and fast Dependency Injection (DI) container with auto-wiring, supporting custom factories and virtual types. It follows an "as modular as possible" approach and requires no separate build or compilation step.

## Installation

```bash
composer require spriebsch/di-container
```

## Basic Usage

To use the DI container, you need a configuration object (implementing `Configuration`) and one or more factory classes (extending `AbstractFactory`).

```php
use spriebsch\diContainer\DIContainer;
use spriebsch\diContainer\Configuration;

// Create a simple configuration
$configuration = new class implements Configuration {};

// Instantiate the container with the configuration and factory classes
$container = new DIContainer($configuration, MyFactory::class);

// Get an instance of a class
$service = $container->get(MyService::class);
```

## Autowiring

The container automatically resolves dependencies by inspecting the constructor of the requested class. Dependencies must be type-hinted with a class or interface name.

```php
class Dependency {}

class Service {
    public function __construct(Dependency $dependency) {}
}

// If MyFactory does not explicitly handle Service or Dependency, 
// the container will try to autowire them.
$service = $container->get(Service::class);
```

Scalar dependencies cannot be auto-wired. You must write a factory method to handle them.

## Custom Factories

You can define custom factory methods in your factory class. The container looks for methods matching the class name (short name or long name).

### Short Name Factory Method

If you request `Project\Namespace\MyService`, the container looks for a method named `MyService` in the factory.

```php
use spriebsch\diContainer\AbstractFactory;

final readonly class MyFactory extends AbstractFactory
{
    protected function MyService(): MyService
    {
        return new MyService('some-parameter');
    }
}
```

### Long Name Factory Method

If multiple classes have the same short name, you can use the fully qualified class name with backslashes replaced by underscores.

```php
final readonly class MyFactory extends AbstractFactory
{
    protected function Project_Namespace_MyService(): MyService
    {
        return new MyService('some-parameter');
    }
}
```

## Virtual Types

Virtual types allow you to request a name that does not correspond to an existing class or interface. This is useful for named instances or configurations.

```php
final readonly class MyFactory extends AbstractFactory
{
    protected function some_virtual_type(): MyService
    {
        return new MyService('virtual');
    }
}

// Requesting the virtual type
$service = $container->get('some_virtual_type');
```

## Passing Parameters

You can pass additional parameters to the `get()` method. These parameters are passed to the factory method.

```php
final readonly class MyFactory extends AbstractFactory
{
    protected function MyService(string $param): MyService
    {
        return new MyService($param);
    }
}

$service = $container->get(MyService::class, 'dynamic-param');
```

## Multiple Factories

You can provide multiple factory classes. The container will search for a handling factory in the order they are provided. If the first factory cannot create the type, it delegates to the next one.

```php
$container = new DIContainer($configuration, FactoryA::class, FactoryB::class);
```

This is particularly useful when using frameworks or libraries that have their own factories.

## Using Configuration

Factories have access to the configuration object.

```php
final readonly class MyFactory extends AbstractFactory
{
    protected function MyService(): MyService
    {
        // $this->configuration is available here
        return new MyService($this->configuration->getSetting());
    }
}
```

## License

Copyright (c) 2025-2026, Stefan Priebsch <stefan@priebsch.de>

For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
