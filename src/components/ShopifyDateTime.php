<?php

namespace davidhirtz\yii2\shopify\components;

use DateTimeZone;
use davidhirtz\yii2\datetime\DateTime;
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
