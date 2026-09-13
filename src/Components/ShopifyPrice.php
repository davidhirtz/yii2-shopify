<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

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

        // Truncating the product of a float loses a cent on most prices: `(int)(19.99 * 100)` is 1998. The subunit
        // is fixed at two digits, which every currency the shop sells in has to have.
        $this->value = (int)round((float)$value * 100);
    }

    public function toInt(): int
    {
        $money = new Money($this->value, new Currency($this->currency));
        return (int)$money->getAmount();
    }
}
