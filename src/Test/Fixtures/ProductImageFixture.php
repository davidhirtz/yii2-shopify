<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\ProductImage;
use yii\test\ActiveFixture;

class ProductImageFixture extends ActiveFixture
{
    public $depends = [ProductFixture::class];
    public $modelClass = ProductImage::class;
}
