<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components;

use DateTimeZone;
use Hirtz\Skeleton\Db\DateTime;
use Yii;

readonly class ShopifyDateTime
{
    public function __construct(private string $value)
    {
    }

    public function toDateTime(): DateTime
    {
        return (new DateTime($this->value))->setTimezone(new DateTimeZone(Yii::$app->getTimeZone()));
    }
}
