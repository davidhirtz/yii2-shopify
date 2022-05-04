<?php

namespace davidhirtz\yii2\shopify\models\base;

use davidhirtz\yii2\shopify\models\ProductImage;
use davidhirtz\yii2\shopify\models\ProductVariant;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class Product
 * @package davidhirtz\yii2\shopify\models\base
 *
 * @property int $id
 * @property int $status
 * @property int $variant_id
 * @property int $image_id
 * @property string $name
 * @property string $content
 * @property string $slug
 * @property string $tags
 * @property string $vendor
 * @property string $product_type
 * @property string $options
 * @property int $image_count
 * @property int $variant_count
 * @property int $last_import_at
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property ProductImage[] $images
 * @property ProductVariant $variant
 * @property ProductVariant[] $variants
 * @property User $updated
 *
 * @method static Product findOne($condition)
 */
class Product extends ActiveRecord
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use StatusAttributeTrait;

    public const STATUS_ARCHIVED = self::STATUS_DISABLED;

    /**
     * @var mixed used when $contentType is set to "html". use array with the first value containing the
     * validator class, following keys can be used to configure the validator, string containing the class
     * name or false for disabling the validation.
     */
    public $htmlValidator = 'davidhirtz\yii2\skeleton\validators\HtmlValidator';

    /**
     * @var string|false the content type, "html" enables html validators and WYSIWYG editor
     */
    public $contentType = 'html';

    /**
     * Constants.
     */
    public const INVENTORY_STATUS_IN_STOCK = 1;
    public const INVENTORY_STATUS_SOLD_OUT = 2;

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
                ['status'],
                'davidhirtz\yii2\skeleton\validators\DynamicRangeValidator',
                'skipOnEmpty' => false,
            ],
            [
                $this->getI18nAttributesNames(['name']),
                'required',
            ],
            array_merge(
                [$this->getI18nAttributesNames(['content'])],
                (array)($this->contentType == 'html' && $this->htmlValidator ? $this->htmlValidator : 'safe')
            ),
            [
                ['id', 'image_id', 'variant_id'],
                'string',
            ],
        ]);
    }

    /**
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('product');
    }

    /**
     * @return ActiveQuery
     */
    public function getVariant()
    {
        return $this->hasOne(ProductVariant::class, ['id' => 'variant_id'])
            ->inverseOf('product');
    }

    /**
     * @return ActiveQuery
     */
    public function getVariants()
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('product');
    }

    /**
     * @return ProductQuery
     */
    public static function find()
    {
        return new ProductQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function getTrailAttributes(): array
    {
        return array_diff($this->attributes(), [
            'last_import_at',
            'updated_at',
            'created_at',
        ]);
    }

    /**
     * @return string
     */
    public function getTrailModelName()
    {
        if ($this->id) {
            return $this->getI18nAttribute('name') ?: Yii::t('skeleton', '{model} #{id}', [
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
        return Yii::t('shopify', 'Product');
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
        return ['/admin/product/update', 'id' => $this->id];
    }

    /**
     * @return array|false
     */
    public function getRoute()
    {
        return false;
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'image_id' => Yii::t('shopify', 'Image'),
            'variant_id' => Yii::t('shopify', 'Variant'),
            'name' => Yii::t('shopify', 'Title'),
            'content' => Yii::t('shopify', 'Description'),
            'slug' => Yii::t('shopify', 'Shopify slug'),
            'vendor' => Yii::t('shopify', 'Vendor'),
            'product_type' => Yii::t('shopify', 'Type'),
        ]);
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return 'Product';
    }

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product');
    }
}