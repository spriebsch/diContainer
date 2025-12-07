<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use Exception;
use ReflectionMethod;
use Throwable;

class ContainerException extends Exception
{
    public static function factoryClassDoesNotExist(string $factoryClass): self
    {
        return new self(
            sprintf(
                'Factory class %s does not exist',
                $factoryClass,
            ),
        );
    }

    public static function factoryIsNoInstanceOfAbstractFactory(string $class): self
    {
        return new self(
            sprintf(
                'Factory %s is no instance of %s',
                $class,
                AbstractFactory::class,
            ),
        );
    }

    public static function virtualTypeDoesNotExist(string $identifier): self
    {
        return new self(sprintf('Factory method for virtual type %s does not exist', $identifier));
    }

    public static function typeDoesNotExist(string $class): self
    {
        return new self(sprintf('Type (class or interface) %s does not exist', $class));
    }

    public static function exceptionWhileCreating(string $class, Throwable $exception): self
    {
        return new self(
            sprintf(
                'Exception "%s" while creating %s',
                $exception->getMessage(),
                $class
            ),
            0,
            $exception
        );
    }

    public static function factoryMethodDidNotReturnObject(string $method, mixed $result): self
    {
        return new self(
            sprintf(
                'Factory method %s does not return object but %s',
                $method,
                gettype($result),
            ),
        );
    }

    public static function numberOfArgumentsMismatch(Type $type, ReflectionMethod $method, array $parameters): self
    {
        return new self(
            sprintf(
                'Type %s has %s parameter(s), method %s expects %s',
                $type->type(),
                count($type->parameters()),
                $method->getName(),
                count($method->getParameters())
            ),
        );
    }
}
