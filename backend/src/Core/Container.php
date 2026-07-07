<?php
namespace App\Core;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function singleton(string $id, callable $factory): void
    {
        $this->bindings[$id] = function () use ($id, $factory) {
            if (!isset($this->instances[$id])) {
                $this->instances[$id] = $factory($this);
            }
            return $this->instances[$id];
        };
    }

    public function get(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            if (class_exists($id)) {
                return new $id();
            }
            throw new \RuntimeException("Service not found: {$id}");
        }
        return ($this->bindings[$id])($this);
    }
}
