<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\migrations;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductVariant;
use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use Yii;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240624153300Json extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        echo "Updating Shopify JSON columns ... ";

        $totalCount = 0;
        $updatedCount = 0;

        $query = Product::find()->select(['id', 'options']);

        foreach ($query->each() as $products) {
            // @phpstan-ignore-next-line
            if (is_string($products->options)) {
                $products->updateAttributes(['options' => json_decode($products->options, true)]);
                $updatedCount++;
            }

            $totalCount++;
        }

        $query = ProductVariant::find()->select(['id', 'presentment_prices']);

        foreach ($query->each() as $products) {
            // @phpstan-ignore-next-line
            if (is_string($products->presentment_prices)) {
                $products->updateAttributes(['presentment_prices' => json_decode($products->presentment_prices, true)]);
                $updatedCount++;
            }

            $totalCount++;
        }

        $updatedCount = Yii::$app->getFormatter()->asInteger($updatedCount);
        $totalCount = Yii::$app->getFormatter()->asInteger($totalCount);

        echo "done.\nUpdated $updatedCount / $totalCount rows.\n";
    }

    public function safeDown(): void
    {
    }
}
