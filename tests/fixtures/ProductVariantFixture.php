<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\fixtures;

use davidhirtz\yii2\shopify\models\ProductVariant;
use yii\test\ActiveFixture;

class ProductVariantFixture extends ActiveFixture
{
    public $modelClass = ProductVariant::class;
}
