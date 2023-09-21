<?php

namespace App\DTO;

class PositionStackResponse
{
    /** @var PositionStackResult[] $data */
    private array $data;

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}