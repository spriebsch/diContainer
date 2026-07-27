<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use Throwable;

final class DIContainer implements Container
{
    public readonly AbstractFactory $factory;

    /** @var array<string, object> */
    private array $instances = [];

    final public function __construct(Configuration $configuration, string ...$factoryClasses)
    {
        $previous = null;

        $factory = null;

        foreach (array_reverse($factoryClasses) as $factoryClass) {
            if (!class_exists($factoryClass, true)) {
                throw ContainerException::factoryClassDoesNotExist($factoryClass);
            }

            if (!is_subclass_of($factoryClass, AbstractFactory::class)) {
                throw ContainerException::factoryIsNoInstanceOfAbstractFactory($factoryClass);
            }

            $factory = new $factoryClass($configuration, $this, $previous);
            $previous = $factory;
        }

        if ($factory === null) {
            throw ContainerException::exceptionWhileCreating('DIContainer', new \RuntimeException('No factory classes provided'));
        }

        $this->factory = $factory;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @param mixed ...$parameters
     * @return T
     */
    final public function get(string $type, mixed ...$parameters): object
    {
        /** @var class-string $typeString */
        $typeString = $type;
        $type = new Type($typeString, ...$parameters);

        if (!$this->has($type)) {
            try {
                $instance = $this->factory->create($type);
            } catch (Throwable $exception) {
                throw ContainerException::cannotCreateType($type->type(), $exception);
            }

            $this->add($type, $instance);
        }

        /** @var T */
        return $this->instances[$type->serialize($this)];
    }

    public function has(Type $type): bool
    {
        return isset($this->instances[$type->serialize($this)]);
    }

    private function add(Type $type, object $instance): void
    {
        $this->instances[$type->serialize($this)] = $instance;
    }
}
