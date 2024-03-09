<?php

namespace Domain\Generics\Collection;

class Collection
{
    protected $data = [];

    protected function add(mixed $item): static
    {
        $this->data[] = $item;
        return $this;
    }

    protected function all(): array
    {
        return $this->data;
    }
}
