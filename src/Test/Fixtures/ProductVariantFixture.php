<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Skeleton\Test\Fixtures\ActiveFixture;
use yii\test\Fixture;

class ProductVariantFixture extends ActiveFixture
{
    /**
     * @var list<class-string<Fixture>>
     */
    public $depends = [ProductFixture::class];
    public $modelClass = ProductVariant::class;
}
