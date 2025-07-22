<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components;

use Money\Currency;
use Money\Money;
use Yii;

readonly class ShopifyPrice
{
    private string $currency;
    private int $value;

    public function __construct(int|float|string $value, ?string $currency = null)
    {
        $this->currency = $currency ?? Yii::$app->get('shopify')->defaultCurrency;
        $this->value = (int)((float)$value * 100);
    }

    public function toInt(): int
    {
        $money = new Money($this->value, new Currency($this->currency));
        return (int)$money->getAmount();
    }
}
