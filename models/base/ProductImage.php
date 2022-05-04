<?php

namespace davidhirtz\yii2\shopify\models\base;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use Yii;

/**
 * Class ProductImage
 * @package davidhirtz\yii2\shopify\models\base
 *
 * @property int $id
 * @property int $product_id
 * @property int $position
 * @property string $alt_text
 * @property int $width
 * @property int $height
 * @property string $src
 * @property DateTime $last_import_at
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property Product $product
 *
 * @method static \davidhirtz\yii2\shopify\models\ProductImage findOne($condition)
 */
class ProductImage extends ActiveRecord
{
    use I18nAttributesTrait;
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->getI18nRules([
            [
                ['product_id', 'position', 'width', 'height', 'src'],
                'required',
            ],
        ]);
    }

    /**
     * @return ProductQuery
     */
    public function getProduct()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Product::class, ['id' => 'entry_id']);
    }

    /**
     * @param Product $product
     */
    public function populateProductRelation($product)
    {
        $this->populateRelation('product', $product);
        $this->product_id = $product->id ?? null;
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'alt_text' => Yii::t('shopify', 'Alt text'),
        ]);
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return 'ProductImage';
    }

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_image');
    }
}