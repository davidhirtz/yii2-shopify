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
 * @property int $inventory_quantity
 * @property DateTime $last_import_at
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
     * @inheritdoc
     */
    public function rules()
    {
        return $this->getI18nRules([
            [
                ['is_taxable', 'requires_shipping'],
                'boolean',
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