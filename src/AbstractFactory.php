<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use Exception;
use ReflectionClass;

abstract readonly class AbstractFactory
{
    final public function __construct(
        protected Configuration $configuration,
        protected DIContainer   $container,
    ) {}

    final public function create(Type $type): object
    {
        if ($type->isVirtual()) {
            $result = $this->handleVirtualType($type);
        } else {
            $this->ensureTypeExists($type);
            $result = $this->handleType($type);
        }

        $this->ensureIsObject($type->type(), $result);

        return $result;
    }

    final public function Container(): Container
    {
        return $this->container;
    }

    final public function Configuration(): Configuration
    {
        return $this->configuration;
    }

    private function createInstance(Type $type): object
    {
        $class = $type->type();

        $reflectionClass = new ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return new $class;
        }

        $parameters = $constructor->getParameters();

        if (count($parameters) === 0) {
            return new $class;
        }

        $dependencies = [];

        foreach ($parameters as $parameter) {

            if (!$parameter->hasType()) {
                throw AutoWireException::constructorParameterHasNoType($class, $parameter);
            }

            $parameterType = $parameter->getType();

            if ($parameterType->isBuiltin()) {
                throw AutoWireException::constructorParameterHasScalarType($class, $parameter);
            }

            $dependency = (new ReflectionClass($parameterType->getName()))->getName();

            $dependencies[] = $this->container->get($dependency);
        }

        return new $class(...$dependencies);
    }

    private function handleType(Type $type): mixed
    {
        $shortNameMethod = $type->shortNameMethod();

        if (method_exists($this, $shortNameMethod)) {
            return $this->$shortNameMethod();
        }

        $longNameMethod = $type->longNameMethod();

        if (method_exists($this, $longNameMethod)) {
            return $this->$longNameMethod();
        }

        try {
            return $this->createInstance($type);
        } catch (Exception $exception) {
            throw ContainerException::exceptionWhileCreating($type->type(), $exception);
        }
    }


    private function handleVirtualType(Type $type): mixed
    {
        if (!method_exists($this, $type->type())) {
            throw ContainerException::virtualTypeDoesNotExist($type->type());
        }

        $method = $type->type();

        $result = $this->$method(...$type->parameters());

        return $result;
    }

    private function ensureIsObject(string $type, mixed $thing): void
    {
        if (!is_object($thing)) {
            throw ContainerException::factoryMethodDidNotReturnObject($type, $thing);
        }
    }

    private function ensureTypeExists(Type $type): void
    {
        if (!$type->exists()) {
            throw ContainerException::typeDoesNotExist($type->type());
        }
    }
}
