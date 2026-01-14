<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\ProductVariant;
use yii\test\ActiveFixture;

class ProductVariantFixture extends ActiveFixture
{
    public $depends = [ProductFixture::class];
    public $modelClass = ProductVariant::class;
}
