<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Skeleton\I18n\Lang;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use Hirtz\Shopify\Models\Traits\ProductRelationTrait;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Hirtz\Skeleton\Validators\RelationValidator;
use Override;
use Yii;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $image_id
 * @property string $name
 * @property int $position
 * @property int $price
 * @property int|null $compare_at_price
 * @property array|null $presentment_prices
 * @property string|null $option_1
 * @property string|null $option_2
 * @property string|null $option_3
 * @property string|null $barcode
 * @property string|null $sku
 * @property bool $is_taxable
 * @property int|null $weight
 * @property string|null $weight_unit
 * @property int|null $inventory_quantity
 * @property bool $inventory_tracked
 * @property string|null $inventory_policy
 * @property int|null $unit_price
 * @property int|null $unit_price_measurement
 * @property DateTime|null $updated_at
 * @property DateTime $created_at
 *
 * @property ProductImage|null $image {@see static::getImage()}
 */
class ProductVariant extends ActiveRecord implements TrailModelInterface
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use ProductRelationTrait;
    use TrailModelTrait {
        TrailModelTrait::formatTrailAttributeValue as parentFormatTrailAttributeValue;
    }

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
                ['id', 'product_id', 'position'],
                'required',
            ],
            [
                ['product_id', 'image_id'],
                RelationValidator::class
            ],
            [
                ['weight', 'unit_price_measurement'],
                'string',
            ],
            [
                ['is_taxable', 'inventory_tracked'],
                'boolean',
            ],
        ]);
    }

    /**
     * @return ActiveQuery<ProductImage>
     */
    public function getImage(): ActiveQuery
    {
        return $this->hasOne(ProductImage::class, [
            'id' => 'image_id',
            'product_id' => 'product_id',
        ]);
    }

    public function getFormattedPrice(): string
    {
        return $this->formatPrice($this->price);
    }

    public function getFormattedCompareAtPrice(): string
    {
        return $this->formatPrice($this->compare_at_price);
    }

    public function getFormattedUnitPrice(): string
    {
        return $this->unit_price
            ? ($this->formatPrice($this->unit_price) . '/' . $this->unit_price_measurement)
            : '';
    }

    protected function formatPrice(?int $value): string
    {
        return $value
            ? Yii::$app->getFormatter()->asCurrency($value / 100, Yii::$app->get('shopify')->defaultCurrency)
            : '';
    }

    public function formatTrailAttributeValue(string $attribute, mixed $value): mixed
    {
        if ($attribute === 'image_id' && $value) {
            $value .= "-$this->product_id";
        }

        return $this->parentFormatTrailAttributeValue($attribute, $value);
    }

    public function getTrailAttributes(): array
    {
        return array_diff($this->attributes(), [
            'position',
            'inventory_quantity',
            'updated_at',
            'created_at',
        ]);
    }

    public function getTrailModelName(): string
    {
        if ($this->id) {
            return $this->getI18nAttribute('name') ?: Lang::t('skeleton', 'COMMON_MODEL_ID', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Lang::t('shopify', 'COMMON_VARIANT');
    }

    public function getTrailModelAdminRoute(): array|false
    {
        return $this->getAdminRoute();
    }

    public function getAdminRoute(): array|false
    {
        return false;
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'name' => Lang::t('shopify', 'PRODUCT_VARIANT_NAME_LABEL'),
            'product_id' => Lang::t('shopify', 'PRODUCT_VARIANT_PRODUCT_ID_LABEL'),
            'image_id' => Lang::t('shopify', 'PRODUCT_VARIANT_IMAGE_ID_LABEL'),
            'position' => Lang::t('shopify', 'PRODUCT_VARIANT_POSITION_LABEL'),
            'price' => Lang::t('shopify', 'PRODUCT_VARIANT_PRICE_LABEL'),
            'compare_at_price' => Lang::t('shopify', 'PRODUCT_VARIANT_COMPARE_AT_PRICE_LABEL'),
            'option_1' => Lang::t('shopify', 'PRODUCT_VARIANT_OPTION_1_LABEL'),
            'option_2' => Lang::t('shopify', 'PRODUCT_VARIANT_OPTION_2_LABEL'),
            'option_3' => Lang::t('shopify', 'PRODUCT_VARIANT_OPTION_3_LABEL'),
            'barcode' => Lang::t('shopify', 'PRODUCT_VARIANT_BARCODE_LABEL'),
            'sku' => Lang::t('shopify', 'PRODUCT_VARIANT_SKU_LABEL'),
            'is_taxable' => Lang::t('shopify', 'PRODUCT_VARIANT_IS_TAXABLE_LABEL'),
            'weight' => Lang::t('shopify', 'PRODUCT_VARIANT_WEIGHT_LABEL'),
            'weight_unit' => Lang::t('shopify', 'PRODUCT_VARIANT_WEIGHT_UNIT_LABEL'),
            'unit_price' => Lang::t('shopify', 'PRODUCT_VARIANT_UNIT_PRICE_LABEL'),
            'unit_price_measurement' => Lang::t('shopify', 'PRODUCT_VARIANT_UNIT_PRICE_MEASUREMENT_LABEL'),
            'inventory_tracked' => Lang::t('shopify', 'PRODUCT_VARIANT_INVENTORY_TRACKED_LABEL'),
            'inventory_quantity' => Lang::t('shopify', 'PRODUCT_VARIANT_INVENTORY_QUANTITY_LABEL'),
            'inventory_policy' => Lang::t('shopify', 'PRODUCT_VARIANT_INVENTORY_POLICY_LABEL'),
        ];
    }

    #[Override]
    public function formName(): string
    {
        return 'ProductVariant';
    }

    #[Override]
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_variant');
    }
}
