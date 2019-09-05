<?php

namespace davidhirtz\yii2\shop\models\base;

use \davidhirtz\yii2\shop\models\Product;
use davidhirtz\yii2\shop\models\queries\AssetQuery;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\shop\models\queries\ProductQuery;
use davidhirtz\yii2\shop\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\models\User;

/**
 * Class Asset.
 * @package davidhirtz\yii2\shop\models\base
 *
 * @property int $id
 * @property int $pim_id
 * @property int $product_id
 * @property int $file_id
 * @property int $position
 * @property string $alt_text
 * @property bool $is_imported
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 * @property File $file
 * @property User $updated
 * @property Product $product
 *
 * @method static \davidhirtz\yii2\shop\models\Asset findOne($condition)
 */
class Asset extends \davidhirtz\yii2\skeleton\db\ActiveRecord
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), []);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->recalculateAssetCount();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $this->recalculateAssetCount();
        parent::afterDelete();
    }

    /**
     * @return ProductQuery
     */
    public function getProduct(): ProductQuery
    {
        return $this->hasOne(Product::class, ['id' => 'entry_id']);
    }

    /**
     * @return AssetQuery
     */
    public function findSiblings()
    {
        return static::find()->where(['product_id' => $this->product_id]);
    }

    /**
     * Recalculates related asset count.
     */
    public function recalculateAssetCount()
    {
        $this->product->asset_count = $this->findSiblings()->count();
        $this->product->update(false);

        if (!$this->file->isDeleted()) {
            $this->file->setAttribute('product_asset_count', static::find()->where(['file_id' => $this->file_id])->count());
            $this->file->update(false);
        }
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return 'Asset';
    }

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_asset');
    }
}