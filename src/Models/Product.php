<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Models\Queries\ProductQuery;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\Interfaces\DraftStatusAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\I18nAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Interfaces\TranslationInterface;
use Hirtz\Skeleton\Models\Traits\AdminModelTrait;
use Hirtz\Skeleton\Models\Traits\DraftStatusAttributeTrait;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Hirtz\Skeleton\Models\Traits\TranslationTrait;
use Hirtz\Skeleton\Models\Traits\UpdatedByUserTrait;
use Hirtz\Skeleton\Validators\DynamicRangeValidator;
use Hirtz\Skeleton\Validators\HtmlValidator;
use Hirtz\Skeleton\Validators\UniqueValidator;
use Override;
use Yii;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int|null $variant_id
 * @property int|null $image_id
 * @property string $name
 * @property string|null $content
 * @property string $slug
 * @property list<string>|null $tags
 * @property string|null $vendor
 * @property string|null $product_type
 * @property list<array{name: string, values: list<string>}>|null $options
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
class Product extends ActiveRecord implements
    DraftStatusAttributeInterface,
    I18nAttributeInterface,
    TrailModelInterface,
    TranslationInterface
{
    use ComponentTrait;
    use AdminModelTrait;
    use I18nAttributesTrait;
    use TranslationTrait;
    use ModuleTrait;
    use DraftStatusAttributeTrait;
    use I18nAttributesTrait;
    use UpdatedByUserTrait;
    use TrailModelTrait {
        TrailModelTrait::formatTrailAttributeValue as parentFormatTrailAttributeValue;
    }

    public const string AUTH_SHOPIFY_PRODUCT = 'shopifyProduct';

    /**
     * @var array<string, mixed>|string|null the validator for the product description, `null` disables the validation
     */
    public array|string|null $htmlValidator = HtmlValidator::class;

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ];
    }

    #[Override]
    public function rules(): array
    {
        return $this->getI18nRules([
            [
                ['status'],
                DynamicRangeValidator::class,
                'skipOnEmpty' => false,
            ],
            [
                ['name', 'slug'],
                'required',
            ],
            [
                ['slug'],
                UniqueValidator::class,
            ],
            [
                ['content'],
                ...(array)($this->htmlValidator ?? 'safe'),
            ],
        ]);
    }

    /**
     * @return ActiveQuery<ProductImage>
     */
    public function getImage(): ActiveQuery
    {
        return $this->hasOne(ProductImage::class, ['id' => 'image_id', 'product_id' => 'id'])
            ->inverseOf('product');
    }

    /**
     * @return ActiveQuery<ProductImage>
     */
    public function getImages(): ActiveQuery
    {
        return $this->hasMany(ProductImage::class, ['product_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('product');
    }

    /**
     * @return ActiveQuery<ProductVariant>
     */
    public function getVariant(): ActiveQuery
    {
        return $this->hasOne(ProductVariant::class, ['id' => 'variant_id'])
            ->inverseOf('product');
    }

    /**
     * @return ActiveQuery<ProductVariant>
     */
    public function getVariants(): ActiveQuery
    {
        return $this->hasMany(ProductVariant::class, ['product_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('product');
    }

    /**
     * @return ProductQuery<self>
     */
    #[Override]
    public static function find(): ProductQuery
    {
        return Yii::createObject(ProductQuery::class, [static::class]);
    }

    public function formatTrailAttributeValue(string $attribute, mixed $value): mixed
    {
        if ($attribute === 'options' && is_array($value)) {
            return array_map(
                fn ($data) => "{$data['name']}: " . implode(', ', $data['values'] ?? []),
                $value
            );
        }

        if ($attribute === 'image_id' && $value) {
            $value .= "-$this->id";
        }

        return $this->parentFormatTrailAttributeValue($attribute, $value);
    }

    /**
     * @return list<string>
     */
    public function getTrailAttributes(): array
    {
        return array_values(array_diff($this->attributes(), [
            'image_count',
            'variant_count',
            'total_inventory_quantity',
            'last_import_at',
            'updated_at',
            'created_at',
        ]));
    }

    public function getAdminType(): string
    {
        return Yii::t('shopify', 'COMMON_PRODUCT');
    }

    public function getAdminRoute(): array|false
    {
        return false;
    }

    /**
     * @return array<int|string, mixed>|false
     */
    public function getRoute(): array|false
    {
        return false;
    }

    public function getShopifyAdminUrl(): string
    {
        $query = "admin/products/$this->id" . ($this->variant_count > 1 ? "/variants/$this->variant_id" : '');
        return static::getShopify()->getShopUrl($query);
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'image_id' => Yii::t('shopify', 'PRODUCT_IMAGE_ID_LABEL'),
            'variant_id' => Yii::t('shopify', 'PRODUCT_VARIANT_ID_LABEL'),
            'name' => Yii::t('shopify', 'PRODUCT_NAME_LABEL'),
            'content' => Yii::t('shopify', 'PRODUCT_CONTENT_LABEL'),
            'slug' => Yii::t('shopify', 'PRODUCT_SLUG_LABEL'),
            'vendor' => Yii::t('shopify', 'PRODUCT_VENDOR_LABEL'),
            'product_type' => Yii::t('shopify', 'PRODUCT_PRODUCT_TYPE_LABEL'),
            'variant_count' => Yii::t('shopify', 'PRODUCT_VARIANT_COUNT_LABEL'),
            'total_inventory_quantity' => Yii::t('shopify', 'PRODUCT_TOTAL_INVENTORY_QUANTITY_LABEL'),
            'last_import_at' => Yii::t('shopify', 'PRODUCT_LAST_IMPORT_AT_LABEL'),
        ];
    }

    #[Override]
    public function formName(): string
    {
        return 'Product';
    }

    public function getTranslationModelClass(): string
    {
        return self::class;
    }

    #[Override]
    public static function tableName(): string
    {
        return '{{%product}}';
    }
}
