<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Migrations;

use Hirtz\Shopify\models\Product;
use Hirtz\Shopify\models\ProductVariant;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
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
