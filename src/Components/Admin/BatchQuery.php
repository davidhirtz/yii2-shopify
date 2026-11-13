<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Iterator;
use Override;

/**
 * @implements Iterator<int, array<string, mixed>|null>
 */
abstract class BatchQuery implements Iterator
{
    use ComponentTrait;
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
        $this->api = static::getShopify()->getAdminApi();
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Override]
    public function current(): ?array
    {
        return $this->data[$this->position] ?? null;
    }

    #[Override]
    public function next(): void
    {
        $this->position++;
    }

    #[Override]
    public function valid(): bool
    {
        if ($this->position === $this->batchSize && $this->currentCursor) {
            $this->data = $this->getData();
            $this->position = 0;
        }

        return isset($this->data[$this->position]);
    }

    #[Override]
    public function key(): int
    {
        return $this->position;
    }

    #[Override]
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
