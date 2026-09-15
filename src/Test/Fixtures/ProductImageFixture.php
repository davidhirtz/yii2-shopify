<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test\Fixtures;

use Hirtz\Shopify\Models\ProductImage;
use Hirtz\Skeleton\Test\Fixtures\ActiveFixture;
use yii\test\Fixture;

class ProductImageFixture extends ActiveFixture
{
    /**
     * @var list<class-string<Fixture>>
     */
    public $depends = [ProductFixture::class];
    public $modelClass = ProductImage::class;
}
