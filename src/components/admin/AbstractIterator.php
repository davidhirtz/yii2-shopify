<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use Iterator;
use Yii;

abstract class AbstractIterator implements Iterator
{
    protected ?string $currentCursor = null;
    private ?array $data = null;
    private int $position = 0;

    public function __construct(
        protected readonly AdminApi $api,
        protected int $batchSize,
        protected ?string $cursor = null,
    )
    {
    }

    public function current(): ?array
    {
        return $this->data[$this->position] ?? null;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function valid(): bool
    {
        if ($this->position === $this->batchSize && $this->currentCursor) {
            $this->data = $this->getData();
            $this->position = 0;
        }

        return isset($this->data[$this->position]);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function rewind(): void
    {
        $this->currentCursor = $this->cursor;
        $this->data = $this->getData();
        $this->position = 0;
    }

    protected function getData(): array
    {
        $data = $this->fetchData();
        $this->currentCursor = end($data)['cursor'] ?? null;

        return $data;
    }

    abstract protected function fetchData(): array;
}
