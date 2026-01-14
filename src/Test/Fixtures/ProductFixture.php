<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\Product;
use yii\test\ActiveFixture;
use yii\test\InitDbFixture;

class ProductFixture extends ActiveFixture
{
    public $depends = [InitDbFixture::class];
    public $modelClass = Product::class;
}
