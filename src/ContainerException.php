<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use Exception;

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

    public static function virtualTypeCannotHaveParameters(string $type): self
    {
        return new self(sprintf('Virtual type %s cannot have parameters', $type));
    }

    public static function typeDoesNotExist(string $class): self
    {
        return new self(sprintf('Type (class or interface) %s does not exist', $class));
    }

    public static function exceptionWhileCreating(string $class, Exception $exception): self
    {
        return new self(sprintf('Exception %s while creating %s', $exception->getMessage(), $class));
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
}
