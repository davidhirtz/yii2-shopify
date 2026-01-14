<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Migrations;

use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

final class M240328135307ProductVariant extends Migration
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
