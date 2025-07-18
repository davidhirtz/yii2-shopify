<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\fixtures;

use davidhirtz\yii2\shopify\models\Product;
use yii\test\ActiveFixture;

class ProductFixture extends ActiveFixture
{
    public $modelClass = Product::class;
}
