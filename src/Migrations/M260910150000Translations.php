<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Migrations;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Models\ProductImage;
use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\Translation;
use yii\db\Migration;

/**
 * Moves the translated attributes of the Shopify models from their `_xx` columns into {@see Translation} records.
 *
 * @noinspection PhpUnused
 */
class M260910150000Translations extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        foreach ($this->getModels() as $model) {
            $this->moveI18nColumnsToTranslations($model);
        }
    }

    public function safeDown(): void
    {
        foreach ($this->getModels() as $model) {
            $this->restoreI18nColumnsFromTranslations($model);
        }

        $product = Product::create();

        foreach ($product->getI18nAttributeNames('slug') as $attributeName) {
            if ($attributeName !== 'slug') {
                $this->createIndex($attributeName, $product::tableName(), $attributeName, true);
            }
        }

        foreach ($product->getI18nAttributeNames('name') as $attributeName) {
            if ($attributeName !== 'name') {
                $this->createIndex($attributeName, $product::tableName(), $attributeName);
            }
        }
    }

    /**
     * @return list<Product|ProductImage|ProductVariant>
     */
    protected function getModels(): array
    {
        return [
            Product::create(),
            ProductImage::create(),
            ProductVariant::create(),
        ];
    }
}
