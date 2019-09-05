<?php

use davidhirtz\yii2\media\models\File;
use \davidhirtz\yii2\shop\models\Product;
use davidhirtz\yii2\shop\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\db\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
use yii\db\Migration;

/**
 * Class M190904193339Shop
 */
class M190904193339Shop extends Migration
{
    use ModuleTrait, MigrationTrait;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema = $this->getDb()->getSchema();

        $this->createTable(Product::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'pim_id' => $this->integer()->unsigned()->null(),
            'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(Product::STATUS_ENABLED),
            'type' => $this->smallInteger()->notNull()->defaultValue(Product::TYPE_DEFAULT),
            'parent_id' => $this->integer()->unsigned()->null(),
            'name' => $this->string(250)->notNull(),
            'content' => $this->text()->null(),
            'sku' => $this->string(100)->null(),
            'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'compare_at_price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'weight' => $this->integer()->unsigned()->null(),
            'quantity' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'inventory_status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'feature_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'asset_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'is_imported' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
            'last_imported_at' => $this->dateTime(),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->getTableOptions());

        $this->createIndex('parent_id', Product::tableName(), 'parent_id');
        $this->createIndex('pim_id', Product::tableName(), 'pim_id', true);
        $this->createIndex('sku', Product::tableName(), 'sku', true);

        $tableName = $schema->getRawTableName(Product::tableName());
        $this->addForeignKey($tableName . '_updated_by_ibfk', Product::tableName(), 'updated_by_user_id', User::tableName(), 'id', 'SET NULL');
        $this->addForeignKey($tableName . '_parent_id_ibfk', Product::tableName(), 'parent_id', Product::tableName(), 'id', 'SET NULL');

        $this->addI18nColumns(Product::tableName(), (new Product)->i18nAttributes);
        $this->addColumn(File::tableName(), 'product_asset_count', $this->smallInteger()->notNull()->defaultValue(0)->after('transformation_count'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(File::tableName(), 'product_asset_count');
        $this->dropTable(Product::tableName());
    }
}
