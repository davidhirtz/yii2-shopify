<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Iterator;
use Yii;

/**
 * @implements Iterator<int, array<string, mixed>|null>
 */
abstract class BatchQuery implements Iterator
{
    protected AdminApi $api;
    protected ?string $currentCursor = null;
    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $data = null;
    private int $position = 0;

    public function __construct(
        protected int $batchSize,
        protected ?string $cursor = null,
    ) {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
    }

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @return list<array<string, mixed>>
     */
    protected function getData(): array
    {
        $data = $this->fetchData();
        $this->currentCursor = end($data)['cursor'] ?? null;

        return $data;
    }

    /**
     * @return list<array<string, mixed>>
     */
    abstract protected function fetchData(): array;
}
