<?php

declare(strict_types=1);

namespace Hirtz\Shopify\migrations;

use Hirtz\Shopify\models\ProductVariant;
use Hirtz\Skeleton\db\traits\MigrationTrait;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240328135307ProductVariant extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->alterColumn(ProductVariant::tableName(), 'inventory_quantity', (string)$this->integer()
            ->unsigned()
            ->null());
    }

    public function safeDown(): void
    {
    }
}
