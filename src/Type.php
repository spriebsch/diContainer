<?php declare(strict_types=1);

namespace spriebsch\diContainer;

use ReflectionClass;

final readonly class Type
{
    private string $type;
    private array $parameters;

    final public function __construct(string $type, mixed ...$parameters)
    {
        $this->type = $type;
        $this->parameters = $parameters;

        // $this->ensureVirtualTypeHasNoParameters();
    }

    public function type(): string
    {
        return $this->type;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function exists(): bool
    {
        return class_exists($this->type(), true) || interface_exists($this->type(), true);
    }

    public function isVirtual(): bool
    {
        return !str_contains($this->type(), '\\');
    }

    public function shortNameMethod(): string
    {
        return new ReflectionClass($this->type())->getShortName();
    }

    public function longNameMethod(): string
    {
        return str_replace('\\', '_', $this->type());
    }

    private function ensureVirtualTypeHasNoParameters(): void
    {
        if (!$this->isVirtual()) {
            return;
        }

        if (count($this->parameters()) !== 0) {
            throw ContainerException::virtualTypeCannotHaveParameters($this->type());
        }
    }
}
