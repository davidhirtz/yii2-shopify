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
 * Class ProductVariant
 * @package davidhirtz\yii2\shopify\models\base
 *
 * @property int $id
 * @property int $product_id
 * @property int $image_id
 * @property string $name
 * @property int $position
 * @property int $price
 * @property int $compare_at_price
 * @property string $presentment_prices
 * @property string $option_1
 * @property string $option_2
 * @property string $option_3
 * @property string $barcode
 * @property string $sku
 * @property bool $is_taxable
 * @property bool $requires_shipping
 * @property int $grams
 * @property int $weight
 * @property string $weight_unit
 * @property bool $track_inventory
 * @property int $inventory_quantity
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property Product $product
 *
 * @method static ProductVariant findOne($condition)
 */
class ProductVariant extends ActiveRecord
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
                ['id', 'product_id', 'position'],
                'required',
            ],
            [
                ['id', 'product_id', 'image_id'],
                'string',
            ],
            [
                ['is_taxable', 'requires_shipping', 'track_inventory'],
                'boolean',
            ],
            [
                ['position', 'inventory_quantity'],
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
        return Yii::t('shopify', 'Variant');
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
        return ['/admin/product-variant/update', 'id' => $this->id];
    }

    /**
     * @return array
     */
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
            'requires_shipping' => Yii::t('shopify', 'Shipping'),
            'grams' => Yii::t('shopify', 'Weight (grams)'),
            'weight' => Yii::t('shopify', 'Weight'),
            'weight_unit' => Yii::t('shopify', 'Weight unit'),
            'track_inventory' => Yii::t('shopify', 'Track quantity'),
            'inventory_quantity' => Yii::t('shopify', 'Quantity'),
        ]);
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return 'ProductVariant';
    }

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_variant');
    }
}