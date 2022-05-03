<?php

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\db\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
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
            'name' => $this->string(250)->notNull(),
            'content' => $this->text()->null(),
            'slug' => $this->string()->notNull(),
            'vendor' => $this->string()->null(),
//            'price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
//            'compare_at_price' => $this->decimal(10, 2)->notNull()->defaultValue(0),
//            'weight' => $this->integer()->unsigned()->null(),
//            'quantity' => $this->integer()->unsigned()->notNull()->defaultValue(0),
//            'inventory_status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'variant_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->getTableOptions());

        $this->addPrimaryKey('id', Product::tableName(), 'id');
        $this->createIndex('slug', Product::tableName(), 'slug', true);

        $tableName = $schema->getRawTableName(Product::tableName());
        $this->addForeignKey($tableName . '_updated_by_ibfk', Product::tableName(), 'updated_by_user_id', User::tableName(), 'id', 'SET NULL');

        $this->addI18nColumns(Product::tableName(), (new Product())->i18nAttributes);
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $this->dropTable(Product::tableName());
    }
}
