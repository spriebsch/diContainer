<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use WeakMap;

final class Container
{
    public readonly AbstractFactory $factory;
    private WeakMap $virtualInstances;
    private array $instances = [];

    final public function __construct(
        public readonly Configuration $configuration,
        public readonly string        $factoryClass,
    )
    {
        if (!class_exists($factoryClass, true)) {
            throw ContainerException::factoryClassDoesNotExist($factoryClass);
        }

        if (!is_subclass_of($factoryClass, AbstractFactory::class)) {
            throw ContainerException::factoryIsNoInstanceOfAbstractFactory($this->factoryClass);
        }

        $factory = new $this->factoryClass($this->configuration, $this);

        $this->factory = $factory;
        $this->virtualInstances = new WeakMap;
    }

    final public function has(Type $type): bool
    {
        return isset($this->virtualInstances[$type]);
    }

    final public function get(string $type, mixed ...$parameters): object
    {
        $type = new Type($type, ...$parameters);

        if (!$this->has($type)) {
            $this->add($type, $this->factory->create($type));
        }

        return $this->virtualInstances[$type];
    }

    private function add(Type $type, object $instance): void
    {
        $this->virtualInstances[$type] = $instance;
    }
}
