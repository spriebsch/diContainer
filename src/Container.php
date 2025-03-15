<?php declare(strict_types=1);

namespace spriebsch\diContainer;

final class Container
{
    public readonly AbstractFactory $factory;
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
    }

    final public function has(Type $type): bool
    {
        return isset($this->instances[spl_object_hash($type)]);
    }

    final public function get(string|Type $type): object
    {
        if (is_string($type)) {
            $type = new Type($type);
        }

        if (!$this->has($type)) {
            $this->add($type, $this->factory->create($type));
        }

        return $this->instances[spl_object_hash($type)];
    }

    private function add(Type $type, object $instance): void
    {
        $this->instances[spl_object_hash($type)] = $instance;
    }
}
