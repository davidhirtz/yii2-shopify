<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Skeleton\Test\Fixtures\ActiveFixture;

class ProductVariantFixture extends ActiveFixture
{
    public $depends = [ProductFixture::class];
    public $modelClass = ProductVariant::class;
}
