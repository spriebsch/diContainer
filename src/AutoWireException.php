<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use ReflectionParameter;

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

    public static function isInterface(string $class): self
    {
        return new self(
            sprintf(
                'Cannot auto-wire: %s is an interface',
                $class,
            ),
        );
    }
}
