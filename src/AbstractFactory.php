<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use Exception;
use ReflectionClass;
use Throwable;

abstract readonly class AbstractFactory
{
    final public function __construct(
        protected Configuration    $configuration,
        protected DIContainer      $container,
        protected ?AbstractFactory $factory = null,
    ) {}

    final public function canCreate(Type $type): bool
    {
        return $this->canHandleVirtualType($type) || $this->canHandleRegularType($type);
    }

    final public function create(Type $type): object
    {
        if (!$this->canCreate($type) && $this->factory !== null) {
            return $this->factory->create($type);
        }

        if ($type->isVirtual()) {
            $result = $this->handleVirtualType($type);
        } else {
            $this->ensureTypeExists($type);
            $result = $this->handleRegularType($type);
        }

        $this->ensureIsObject($type->type(), $result);

        /** @var object $result */
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

    private function canHandleRegularType(Type $type): bool
    {
        $shortNameMethod = $type->shortNameMethod();
        $longNameMethod = $type->longNameMethod();

        if ($shortNameMethod === null || $longNameMethod === null) {
            return false;
        }

        return method_exists($this, $shortNameMethod) || method_exists($this, $longNameMethod);
    }

    private function handleRegularType(Type $type): mixed
    {
        try {
            $shortNameMethod = $type->shortNameMethod();

            if ($shortNameMethod !== null && method_exists($this, $shortNameMethod)) {
                $this->ensureParameterCountMatches($shortNameMethod, $type);

                return $this->$shortNameMethod(...$type->parameters());
            }

            $longNameMethod = $type->longNameMethod();

            if ($longNameMethod !== null && method_exists($this, $longNameMethod)) {
                $this->ensureParameterCountMatches($longNameMethod, $type);

                return $this->$longNameMethod(...$type->parameters());
            }

            return $this->autoWire($type);
        } catch (Throwable $exception) {
            throw ContainerException::exceptionWhileCreating($type->type(), $exception);
        }
    }

    private function autoWire(Type $type): object
    {
        /** @var class-string $class */
        $class = $type->type();

        $reflectionClass = new ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
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

            /** @var \ReflectionNamedType $parameterType */
            $parameterType = $parameter->getType();

            if ($parameterType->isBuiltin()) {
                throw AutoWireException::constructorParameterHasScalarType($class, $parameter);
            }

            /** @var class-string $dependency */
            $dependency = $parameterType->getName();

            $dependencies[] = $this->container->get($dependency);
        }

        return new $class(...$dependencies);
    }

    private function canHandleVirtualType(Type $type): bool
    {
        return method_exists($this, $type->type());
    }

    private function handleVirtualType(Type $type): mixed
    {
        if (!method_exists($this, $type->type())) {
            throw ContainerException::virtualTypeDoesNotExist($type->type());
        }

        try {
            $method = $type->type();

            $result = $this->$method(...$type->parameters());

            return $result;
        } catch (Exception $exception) {
            throw ContainerException::exceptionWhileCreating($type->type(), $exception);
        }
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

    private function ensureParameterCountMatches(string $method, Type $type): void
    {
        $class = new ReflectionClass($this);
        $method = $class->getMethod($method);
        $parameters = $method->getParameters();

        if (count($parameters) === 0) {

            if (count($type->parameters()) > 0) {
                throw ContainerException::numberOfArgumentsMismatch($type, $method, $parameters);
            }

            return;
        }

        $lastParameter = end($parameters);

        if ($lastParameter->isVariadic()) {
            return;
        }

        if (count($type->parameters()) > count($parameters)) {
            throw ContainerException::numberOfArgumentsMismatch($type, $method, $parameters);
        }

        if (count($type->parameters()) < count($parameters)) {
            throw ContainerException::numberOfArgumentsMismatch($type, $method, $parameters);
        }
    }
}
