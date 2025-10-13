<?php

namespace App\Core;

use Closure;
use InvalidArgumentException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

class Container
{
    /**
     * @var array<string, Closure|object|string>
     */
    private array $bindings = [];

    /**
     * @param Closure|object|string $concrete
     */
    public function set(string $id, mixed $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings);
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            if (class_exists($id)) {
                return $this->build($id);
            }

            throw new InvalidArgumentException("Service '{$id}' is not bound in the container.");
        }

        $concrete = $this->bindings[$id];

        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        if (is_string($concrete) && class_exists($concrete)) {
            return $this->build($concrete);
        }

        return $concrete;
    }

    private function build(string $class): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
                $message = sprintf(
                    "Union or intersection types are not supported when resolving '%s' on '%s'.",
                    $parameter->getName(),
                    $class
                );

                throw new InvalidArgumentException($message);
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new InvalidArgumentException("Cannot resolve dependency '{$parameter->getName()}' for class '{$class}'.");
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
