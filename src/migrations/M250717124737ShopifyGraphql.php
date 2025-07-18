<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\migrations;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductImage;
use davidhirtz\yii2\shopify\models\ProductVariant;
use yii\db\Expression;
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

        $schema = $this->getDb()->getSchema()->getTableSchema(ProductVariant::tableName());

        if ($schema->getColumn('inventory_management')) {
            $this->addColumn(ProductVariant::tableName(), 'inventory_tracked', (string)$this->boolean()
                ->unsigned()
                ->notNull()
                ->defaultValue(false)
                ->after('inventory_quantity'));

            $this->update(
                ProductVariant::tableName(),
                ['inventory_tracked' => true],
                '[[inventory_management]] IS NULL OR [[inventory_management]]="shopify"'
            );

            $this->dropColumn(ProductVariant::tableName(), 'inventory_management');
        }

        if ($schema->getColumn('grams')) {
            $this->dropColumn(ProductVariant::tableName(), 'grams');
        }

        $this->addColumn(ProductVariant::tableName(), 'unit_price', (string)$this->integer()
            ->unsigned()
            ->null()
            ->after('inventory_policy'));

        $this->addColumn(ProductVariant::tableName(), 'unit_price_measurement', (string)$this->string(10)
            ->null()
            ->after('unit_price'));

        $this->update(ProductVariant::tableName(), [
            'price' => new Expression('[[price]] * 100'),
            'compare_at_price' => new Expression('[[compare_at_price]] * 100'),
        ]);

        $this->alterColumn(ProductVariant::tableName(), 'price', (string)$this->integer()
            ->unsigned()
            ->notNull());

        $this->alterColumn(ProductVariant::tableName(), 'compare_at_price', (string)$this->integer()
            ->unsigned()
            ->null());

        parent::safeUp();
    }

    public function safeDown(): void
    {
        $this->dropColumn(ProductVariant::tableName(), 'unit_price_measurement');
        $this->dropColumn(ProductVariant::tableName(), 'unit_price');
    }
}
