<?php declare(strict_types=1);

namespace spriebsch\diContainer;

interface Container
{
    /**
     * @template T of object
     * @param class-string<T> $type
     * @param mixed ...$parameters
     * @return T
     */
    public function get(string $type, mixed ...$parameters): object;
}
