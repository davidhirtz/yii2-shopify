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
        foreach ($this->getTables() as $table => $modelClass) {
            $this->moveI18nColumnsToTranslations($table, $modelClass);
        }
    }

    public function safeDown(): void
    {
        foreach ($this->getTables() as $table => $modelClass) {
            $this->restoreI18nColumnsFromTranslations($table, $modelClass);
        }

        foreach ($this->getI18nColumns(Product::tableName()) as $column => [$attribute]) {
            if ($attribute === 'slug' || $attribute === 'name') {
                $this->createIndex($column, Product::tableName(), $column, $attribute === 'slug');
            }
        }
    }

    /**
     * @return array<string, class-string>
     */
    protected function getTables(): array
    {
        return [
            Product::tableName() => Product::class,
            ProductImage::tableName() => ProductImage::class,
            ProductVariant::tableName() => ProductVariant::class,
        ];
    }
}
