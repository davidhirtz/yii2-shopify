<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

readonly class ShopifyId
{
    public function __construct(private string $id)
    {
    }

    public function toInt(): int
    {
        return (int)substr(strrchr($this->id, '/'), 1);
    }
}
