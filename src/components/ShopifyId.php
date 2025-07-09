<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components;

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
