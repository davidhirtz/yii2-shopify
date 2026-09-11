<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\ProductImage;
use Hirtz\Skeleton\Test\Fixtures\ActiveFixture;

class ProductImageFixture extends ActiveFixture
{
    public $depends = [ProductFixture::class];
    public $modelClass = ProductImage::class;
}
