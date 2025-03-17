<?php declare(strict_types=1);

namespace spriebsch\diContainer;

interface Container
{
    public function get(string $type, mixed ...$parameters): object;

    public function delegateGet(Container $wrapper, string $type, mixed ...$parameters): object;
}
