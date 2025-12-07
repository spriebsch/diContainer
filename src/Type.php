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
    }

    public function type(): string
    {
        return $this->type;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function serialize(Container $container): string
    {
        $parameters = [];

        foreach ($this->parameters() as $parameter) {
            if (is_object($parameter)) {
                $parameters[] = new Type($parameter::class);
            } else {
                $parameters[] = $parameter;
            }
        }

        return serialize(
            [
                $this->type(),
                $parameters
            ]
        );
    }

    public function exists(): bool
    {
        return class_exists($this->type(), true) || interface_exists($this->type(), true);
    }

    public function isVirtual(): bool
    {
        return !$this->exists();
    }

    public function shortNameMethod(): ?string
    {
        if ($this->isVirtual()) {
            return null;
        }

        return new ReflectionClass($this->type())->getShortName();
    }

    public function longNameMethod(): ?string
    {
        if ($this->isVirtual()) {
            return null;
        }

        return str_replace('\\', '_', $this->type());
    }
}
