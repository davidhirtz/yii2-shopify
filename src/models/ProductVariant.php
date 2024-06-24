<?php

namespace davidhirtz\yii2\shopify\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\shopify\models\traits\ProductRelationTrait;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\models\traits\I18nAttributesTrait;
use Yii;

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
 * @property int|null $grams
 * @property int|null $weight
 * @property string|null $weight_unit
 * @property string|null $inventory_management
 * @property int|null $inventory_quantity
 * @property string|null $inventory_policy
 * @property DateTime|null $updated_at
 * @property DateTime $created_at
 */
class ProductVariant extends ActiveRecord
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use ProductRelationTrait;

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
        return $this->getI18nRules([
            [
                ['id'],
                'unique',
            ],
            [
                ['id', 'product_id', 'position'],
                'required',
            ],
            [
                ['id', 'product_id', 'image_id'],
                'string',
            ],
            [
                ['is_taxable'],
                'boolean',
            ],
            [
                ['position', 'inventory_quantity'],
                'number',
                'integerOnly' => true,
            ],
        ]);
    }

    public function getTrailAttributes(): array
    {
        return array_diff($this->attributes(), [
            'updated_at',
            'created_at',
        ]);
    }

    public function getTrailModelName(): string
    {
        if ($this->product_id) {
            return $this->product->getI18nAttribute('name') ?: Yii::t('skeleton', '{model} #{id}', [
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

    public function getTrailModelAdminRoute(): array|false
    {
        return $this->getAdminRoute();
    }

    public function getAdminRoute(): array|false
    {
        return ['/admin/product/update', 'id' => $this->product_id];
    }

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
            'grams' => Yii::t('shopify', 'Weight (grams)'),
            'weight' => Yii::t('shopify', 'Weight'),
            'weight_unit' => Yii::t('shopify', 'Weight unit'),
            'inventory_management' => Yii::t('shopify', 'Inventory management'),
            'inventory_quantity' => Yii::t('shopify', 'Quantity'),
            'inventory_policy' => Yii::t('shopify', 'Inventory policy'),
        ]);
    }

    public function formName(): string
    {
        return 'ProductVariant';
    }

    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_variant');
    }
}
