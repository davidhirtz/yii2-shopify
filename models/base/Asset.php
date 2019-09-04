<?php

namespace davidhirtz\yii2\shop\models\base;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\shop\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;

/**
 * Class Asset.
 * @package davidhirtz\yii2\shop\models\base
 *
 * @property int $id
 * @property int $entry_id
 * @property int $section_id
 * @property int $file_id
 * @property int $position
 * @property string $name
 * @property string $content
 * @property string $alt_text
 * @property string $link
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 * @property File $file
 * @property User $updated
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
     * Recalculates related asset count.
     */
    public function recalculateAssetCount()
    {
//        $parent = $this->getParent();
//        $parent->asset_count = $this->findSiblings()->count();
//        $parent->update(false);
//
//        if (!$this->file->isDeleted()) {
//            $this->file->setAttribute('shop_asset_count', static::find()->where(['file_id' => $this->file_id])->count());
//            $this->file->update(false);
//        }
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
        return static::getModule()->getTableName('shop_asset');
    }
}