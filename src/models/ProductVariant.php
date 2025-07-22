<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\shopify\models\traits\ProductRelationTrait;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\models\traits\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\validators\RelationValidator;
use Override;
use Yii;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property int $product_id
 * @property int $image_id
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
class ProductVariant extends ActiveRecord
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use ProductRelationTrait;

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

        /** @var TrailBehavior $behavior */
        $behavior = $this->getBehavior('TrailBehavior');
        return $behavior->formatTrailAttributeValue($attribute, $value);
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
            return $this->getI18nAttribute('name') ?: Yii::t('skeleton', '{model} #{id}', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Yii::t('shopify', 'Variant');
    }

    #[Override]
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'name' => Yii::t('shopify', 'Title'),
            'product_id' => Yii::t('shopify', 'Product'),
            'image_id' => Yii::t('shopify', 'Image'),
            'position' => Yii::t('shopify', 'Position'),
            'price' => Yii::t('shopify', 'Price'),
            'compare_at_price' => Yii::t('shopify', 'Compare at price'),
            'option_1' => Yii::t('shopify', 'Option 1'),
            'option_2' => Yii::t('shopify', 'Option 2'),
            'option_3' => Yii::t('shopify', 'Option 3'),
            'barcode' => Yii::t('shopify', 'Barcode (ISBN, UPC, GTIN, etc.)'),
            'sku' => Yii::t('shopify', 'SKU (Stock Keeping Unit)'),
            'is_taxable' => Yii::t('shopify', 'Taxable'),
            'weight' => Yii::t('shopify', 'Weight'),
            'weight_unit' => Yii::t('shopify', 'Weight unit'),
            'unit_price' => Yii::t('shopify', 'Unit price'),
            'unit_price_measurement' => Yii::t('shopify', 'Unit price measurement'),
            'inventory_tracked' => Yii::t('shopify', 'Inventory tracking'),
            'inventory_quantity' => Yii::t('shopify', 'Quantity'),
            'inventory_policy' => Yii::t('shopify', 'Inventory policy'),
        ]);
    }

    #[Override]
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_variant');
    }
}
