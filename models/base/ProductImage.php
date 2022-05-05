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
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'DateTimeBehavior' => 'davidhirtz\yii2\datetime\DateTimeBehavior',
            'TrailBehavior' => 'davidhirtz\yii2\skeleton\behaviors\TrailBehavior',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->getI18nRules([
            [
                ['id', 'product_id', 'position', 'width', 'height', 'src'],
                'required',
            ],
            [
                ['id', 'product_id'],
                'string',
            ],
            [
                ['position', 'width', 'height'],
                'number',
                'integerOnly' => true,
            ],
        ]);
    }

    /**
     * @return ProductQuery
     */
    public function getProduct()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Product::class, ['id' => 'product_id']);
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
     * @param array $params
     * @return string
     */
    public function getUrl($params = [])
    {
        return $this->src . ($params ? ((strpos($this->src, '?') ? '&' : '?') . http_build_query($params)) : '');
    }

    /**
     * @return array
     */
    public function getTrailAttributes(): array
    {
        return array_diff($this->attributes(), [
            'updated_at',
            'created_at',
        ]);
    }

    /**
     * @return string
     */
    public function getTrailModelName()
    {
        if ($this->product_id) {
            return $this->product->getI18nAttribute('name') ?: Yii::t('skeleton', '{model} #{id}', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    /**
     * @return string
     */
    public function getTrailModelType(): string
    {
        return Yii::t('shopify', 'Image');
    }

    /**
     * @return array|false
     */
    public function getTrailModelAdminRoute()
    {
        return $this->getAdminRoute();
    }

    /**
     * @return mixed
     */
    public function getAdminRoute()
    {
        return ['/admin/product/update', 'id' => $this->product_id];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'position' => Yii::t('shopify', 'Position'),
            'product_id' => Yii::t('shopify', 'Product'),
            'alt_text' => Yii::t('shopify', 'Alt text'),
            'weight' => Yii::t('shopify', 'Weight'),
            'height' => Yii::t('shopify', 'Height'),
            'src' => Yii::t('shopify', 'URL'),
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