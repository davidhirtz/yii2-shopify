<?php

namespace davidhirtz\yii2\shopify\migrations;

use davidhirtz\yii2\shopify\models\base\ProductImage;
use davidhirtz\yii2\shopify\models\base\ProductVariant;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\db\MigrationTrait;
use yii\db\Migration;

/**
 * Class M190904193339Shopify
 */
class M190904193339Shopify extends Migration
{
    use MigrationTrait;
    use ModuleTrait;

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $schema = $this->getDb()->getSchema();

        $this->createTable(Product::tableName(), [
            'id' => $this->bigInteger()->unsigned()->notNull(),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(Product::STATUS_ENABLED),
            'variant_id' => $this->bigInteger()->unsigned()->null(),
            'image_id' => $this->bigInteger()->unsigned()->null(),
            'name' => $this->string()->notNull(),
            'content' => $this->text()->null(),
            'slug' => $this->string()->notNull(),
            'tags' => $this->string()->null(),
            'vendor' => $this->string()->null(),
            'product_type' => $this->string()->null(),
            'options' => $this->json()->null(),
            'image_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'variant_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'last_import_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->getTableOptions());

        $this->createTable(ProductImage::tableName(), [
            'id' => $this->bigInteger()->unsigned()->notNull(),
            'product_id' => $this->bigInteger()->unsigned()->notNull(),
            'position' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'alt_text' => $this->string()->null(),
            'width' => $this->smallInteger()->unsigned()->null(),
            'height' => $this->smallInteger()->unsigned()->null(),
            'src' => $this->string()->null(),
            'last_import_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->getTableOptions());

        $this->createTable(ProductVariant::tableName(), [
            'id' => $this->bigInteger()->unsigned()->notNull(),
            'product_id' => $this->bigInteger()->unsigned()->notNull(),
            'image_id' => $this->bigInteger()->unsigned()->null(),
            'name' => $this->string()->notNull(),
            'position' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'compare_at_price' => $this->decimal(10, 2)->null(),
            'presentment_prices' => $this->json()->null(),
            'option_1' => $this->string()->null(),
            'option_2' => $this->string()->null(),
            'option_3' => $this->string()->null(),
            'barcode' => $this->string()->null(),
            'sku' => $this->string()->null(),
            'is_taxable' => $this->boolean()->defaultValue(false),
            'requires_shipping' => $this->boolean()->defaultValue(false),
            'grams' => $this->decimal(10, 2)->unsigned()->null(),
            'weight' => $this->string()->null(),
            'weight_unit' => $this->string(2)->null(),
            'inventory_quantity' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'last_import_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->getTableOptions());

        $this->addI18nColumns(Product::tableName(), (new Product())->i18nAttributes);
        $this->addI18nColumns(ProductImage::tableName(), (new ProductImage())->i18nAttributes);
        $this->addI18nColumns(ProductVariant::tableName(), (new ProductVariant())->i18nAttributes);

        $this->addPrimaryKey('id', Product::tableName(), 'id');

        foreach ((new Product())->getI18nAttributesNames('slug') as $attributesName) {
            $this->createIndex($attributesName, Product::tableName(), $attributesName, true);
        }

        foreach ((new Product())->getI18nAttributesNames('name') as $attributesName) {
            $this->createIndex($attributesName, Product::tableName(), $attributesName);
        }

        $this->addPrimaryKey('id', ProductImage::tableName(), 'id');
        $this->createIndex('product_id', ProductImage::tableName(), ['product_id', 'position']);

        $this->addPrimaryKey('id', ProductVariant::tableName(), 'id');
        $this->createIndex('product_id', ProductVariant::tableName(), ['product_id', 'position']);

        $tableName = $schema->getRawTableName(Product::tableName());
        $this->addForeignKey($tableName . '_variant_id_ibfk', Product::tableName(), 'image_id', ProductVariant::tableName(), 'id', 'SET NULL');
        $this->addForeignKey($tableName . '_image_id_ibfk', Product::tableName(), 'image_id', ProductImage::tableName(), 'id', 'SET NULL');

        $tableName = $schema->getRawTableName(ProductImage::tableName());
        $this->addForeignKey($tableName . '_product_id_ibfk', ProductImage::tableName(), 'product_id', Product::tableName(), 'id', 'CASCADE');

        $tableName = $schema->getRawTableName(ProductVariant::tableName());
        $this->addForeignKey($tableName . '_product_id_ibfk', ProductVariant::tableName(), 'product_id', Product::tableName(), 'id', 'CASCADE');
        $this->addForeignKey($tableName . '_image_id_ibfk', ProductVariant::tableName(), 'image_id', ProductImage::tableName(), 'id', 'SET NULL');

        $this->addI18nColumns(Product::tableName(), (new Product())->i18nAttributes);
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $schema = $this->getDb()->getSchema();

        $tableName = $schema->getRawTableName(Product::tableName());
        $this->dropForeignKey($tableName . '_variant_id_ibfk', Product::tableName());
        $this->dropForeignKey($tableName . '_image_id_ibfk', Product::tableName());

        $tableName = $schema->getRawTableName(ProductVariant::tableName());
        $this->dropForeignKey($tableName . '_image_id_ibfk', ProductVariant::tableName());

        $this->dropTable(ProductImage::tableName());
        $this->dropTable(ProductVariant::tableName());
        $this->dropTable(Product::tableName());
    }
}
