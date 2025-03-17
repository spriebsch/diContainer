<?php declare(strict_types=1);

namespace spriebsch\diContainer;

interface Container
{
    public function has(Type $type): bool;

    public function get(string $type, mixed ...$parameters): object;
}
