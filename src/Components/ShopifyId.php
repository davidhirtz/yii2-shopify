<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

readonly class ShopifyId
{
    public function __construct(private string $id)
    {
    }

    /**
     * A GraphQL id is a `gid://shopify/Product/1` path, a webhook payload carries the bare number — and
     * `strrchr()` returns `false` for the latter, which `substr()` refuses.
     */
    public function toInt(): int
    {
        $position = strrpos($this->id, '/');
        return (int)($position === false ? $this->id : substr($this->id, $position + 1));
    }
}
