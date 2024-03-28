<?php

namespace davidhirtz\yii2\shopify\migrations;

use davidhirtz\yii2\shopify\models\ProductVariant;
use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240328135307ProductVariant extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->alterColumn(ProductVariant::tableName(), 'inventory_quantity', $this->integer()
            ->unsigned()
            ->null());
    }

    public function safeDown(): void
    {
    }
}