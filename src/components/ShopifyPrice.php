<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components;

use Money\Currency;
use Money\Money;

readonly class ShopifyPrice
{
    private int $value;

    public function __construct(int|float|string $value, private string $currency)
    {
        $this->value = (int)((float)$value * 100);
    }

    public function toInt(): int
    {
        $money = new Money($this->value, new Currency($this->currency));
        return (int)$money->getAmount();
    }
}
