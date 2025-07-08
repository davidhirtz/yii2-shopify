<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\models\collections;

use davidhirtz\yii2\shopify\components\apis\ShopifyAdminApi;
use Iterator;
use Yii;

class ShopifyApiProductCollection implements Iterator
{
    private ?string $currentCursor = null;
    private ?array $data = null;
    private int $position = 0;

    public function __construct(
        protected ShopifyAdminApi $api,
        protected int $batchSize = 20,
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
        Yii::debug("Rewind", __METHOD__);

        $this->currentCursor = $this->cursor;
        $this->data = $this->getData();
        $this->position = 0;
    }

    protected function getData(): array
    {
        $data = $this->api->fetchProducts($this->batchSize, $this->currentCursor);
        $this->currentCursor = end($data)['cursor'] ?? null;

        return $data;
    }
}
