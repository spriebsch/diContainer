<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use ReflectionParameter;
use Throwable;

class AutoWireException extends ContainerException
{
    public static function constructorParameterHasNoType(string $class, ReflectionParameter $parameter): self
    {
        return new self(
            sprintf(
                'Cannot auto-wire: constructor parameter %s of %s has no type',
                $parameter->getName(),
                $class,
            ),
        );
    }

    public static function constructorParameterHasScalarType(string $class, ReflectionParameter $parameter): self
    {
        return new self(
            sprintf(
                'Cannot auto-wire: constructor parameter %s of %s has scalar type',
                $parameter->getName(),
                $class,
            ),
        );
    }

    public static function cannotCreate(string $class, ReflectionParameter $parameter, Throwable $exception): self
    {
        return new self(
            sprintf(
                'Cannot auto-wire %s: constructor parameter %s: %s',
                $class,
                $parameter->getName(),
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
