<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\migrations;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductImage;
use davidhirtz\yii2\shopify\models\ProductVariant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M250717124737ShopifyGraphql extends Migration
{
    public function safeUp(): void
    {
        $this->dropForeignKey('product_image_id_ibfk', Product::tableName());
        $this->dropForeignKey('product_variant_image_id_ibfk', ProductVariant::tableName());

        $this->dropPrimaryKey('id', ProductImage::tableName());
        $this->addPrimaryKey('pk', ProductImage::tableName(), ['id', 'product_id']);

        $this->addForeignKey(
            'product_image_id_ibfk',
            Product::tableName(),
            'image_id',
            ProductImage::tableName(),
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'product_variant_image_id_ibfk',
            ProductVariant::tableName(),
            'image_id',
            ProductImage::tableName(),
            'id',
            'SET NULL'
        );

        parent::safeUp();
    }
}