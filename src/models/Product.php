<?php

namespace davidhirtz\yii2\shopify\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\models\interfaces\DraftStatusAttributeInterface;
use davidhirtz\yii2\skeleton\models\traits\DraftStatusAttributeTrait;
use davidhirtz\yii2\skeleton\models\traits\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\models\traits\UpdatedByUserTrait;
use davidhirtz\yii2\skeleton\validators\DynamicRangeValidator;
use davidhirtz\yii2\skeleton\validators\HtmlValidator;
use Yii;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int $status
 * @property int|null $variant_id
 * @property int|null $image_id
 * @property string $name
 * @property string|null $content
 * @property string $slug
 * @property string|null $tags
 * @property string|null $vendor
 * @property string|null $product_type
 * @property string|null $options
 * @property int $image_count
 * @property int $variant_count
 * @property int $total_inventory_quantity
 * @property DateTime $last_import_at
 * @property DateTime|null $updated_at
 * @property DateTime $created_at
 *
 * @property-read ProductImage|null $image {@see static::getImage()}
 * @property-read ProductImage[] $images {@see static::getImages()}
 * @property-read ProductVariant|null $variant {@see static::getVariant()}
 * @property-read ProductVariant[] $variants {@see static::getVariants()}
 */
class Product extends ActiveRecord implements DraftStatusAttributeInterface
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use DraftStatusAttributeTrait;
    use UpdatedByUserTrait;

    public const STATUS_ARCHIVED = self::STATUS_DISABLED;

    public const AUTH_PRODUCT_UPDATE = 'shopifyProductUpdate';

    /**
     * @var array|string used when `$contentType`is set to "html". use an array with the first value containing a
     * validator class, following keys can be used to configure the validator, string containing the class name or
     * false for disabling the validation.
     */
    public array|string $htmlValidator = HtmlValidator::class;

    /**
     * @var string|false the content type, "html" enables html validators and WYSIWYG editor
     */
    public string|false $contentType = 'html';

    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            ...$this->getI18nRules([
                [
                    ['status'],
                    DynamicRangeValidator::class,
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
            ]),
        ];
    }

    public function getImage(): ActiveQuery
    {
        return $this->hasOne(ProductImage::class, ['id' => 'image_id'])
            ->inverseOf('product');
    }

    public function getImages(): ActiveQuery
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('product');
    }

    public function getVariant(): ActiveQuery
    {
        return $this->hasOne(ProductVariant::class, ['id' => 'variant_id'])
            ->inverseOf('product');
    }

    public function getVariants(): ActiveQuery
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('product');
    }

    public static function find(): ProductQuery
    {
        return Yii::createObject(ProductQuery::class, [static::class]);
    }

    public function getTrailAttributes(): array
    {
        return array_diff($this->attributes(), [
            'last_import_at',
            'updated_at',
            'created_at',
        ]);
    }

    public function getTrailModelName(): string
    {
        if ($this->id) {
            return $this->getI18nAttribute('name') ?: Yii::t('skeleton', '{model} #{id}', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Yii::t('shopify', 'Product');
    }

    public function getTrailModelAdminRoute(): array|string|false
    {
        return $this->getAdminRoute();
    }

    public function getAdminRoute(): array|string|false
    {
        return $this->getShopifyAdminUrl();
    }

    public function getRoute(): array|false
    {
        return false;
    }

    protected function getShopifyAdminUrl(): string
    {
        return static::getModule()->getShopUrl("admin/products/$this->id");
    }

    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'image_id' => Yii::t('shopify', 'Image'),
            'variant_id' => Yii::t('shopify', 'Variant'),
            'name' => Yii::t('shopify', 'Title'),
            'content' => Yii::t('shopify', 'Description'),
            'slug' => Yii::t('shopify', 'Shopify slug'),
            'vendor' => Yii::t('shopify', 'Vendor'),
            'product_type' => Yii::t('shopify', 'Type'),
            'variant_count' => Yii::t('shopify', 'Variants'),
            'total_inventory_quantity' => Yii::t('shopify', 'Inventory')
        ];
    }

    public function formName(): string
    {
        return 'Product';
    }

    public static function tableName(): string
    {
        return static::getModule()->getTableName('product');
    }
}
