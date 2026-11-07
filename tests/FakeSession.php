<?php

declare(strict_types=1);

namespace Youvision\ThinkSocial\Tests;

class FakeSession
{
    protected array $data = [];

    public function set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    public function delete(string $name): void
    {
        unset($this->data[$name]);
    }
}