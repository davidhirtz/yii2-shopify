<?php

namespace davidhirtz\yii2\shop\models\base;

use davidhirtz\yii2\shop\models\queries\ProductQuery;
use davidhirtz\yii2\shop\modules\ModuleTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\db\TypeAttributeTrait;
use davidhirtz\yii2\skeleton\models\queries\UserQuery;
use davidhirtz\yii2\skeleton\models\User;
use Yii;

/**
 * Class Product.
 * @package app\modules\shop\models
 * @see Product
 *
 * @property int $id
 * @property int $pim_id
 * @property int $status
 * @property int $type
 * @property int $parent_id
 * @property string $name
 * @property string $content
 * @property string $sku
 * @property float $price
 * @property float $compare_at_price
 * @property int $weight
 * @property int $quantity
 * @property int $inventory_status
 * @property int $feature_count
 * @property int $asset_count
 * @property bool $is_imported
 * @property int $updated_by_user_id
 * @property int $created_by_user_id
 * @property DateTime $last_imported_at
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * // * @property Feature[] $features
 * // * @property ProductFeature[] $productFeatures
 * @property User $updated
 *
 * @method static Product findOne($condition)
 */
class Product extends ActiveRecord
{
    use I18nAttributesTrait, StatusAttributeTrait, TypeAttributeTrait,
        ModuleTrait;

    /**
     * @var string
     */
    public $htmlValidator = 'davidhirtz\yii2\skeleton\validators\HtmlValidator';

    /**
     * @var bool|string
     */
    public $contentType = false;

    /**
     * Constants.
     */
    const INVENTORY_STATUS_IN_STOCK = 1;
    const INVENTORY_STATUS_SOLD_OUT = 2;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->getI18nRules([
            [
                $this->getI18nAttributeNames(['name']),
                'required',
            ],
            [
                $this->getI18nAttributeNames(['name']),
                'filter',
                'filter' => 'trim',
            ],
            [
                $this->getI18nAttributeNames(['content']),
                $this->contentType == 'html' ? $this->htmlValidator : 'safe',
            ],
            [
                ['quantity'],
                'number',
                'integerOnly' => true,
                'min' => 0,
            ],
            [
                ['inventory_status'],
                'in',
                'range' => array_keys(static::getInventoryStatuses()),
            ],
            [
                ['is_imported'],
                'boolean',
            ],
        ]);
    }

    /***********************************************************************
     * Relations.
     ***********************************************************************/

    /**
     * @return UserQuery|\yii\db\ActiveQuery
     */
    public function getCreated()
    {
        return $this->hasOne(User::class, ['id' => 'created_by_user_id']);
    }

//    /**
//     * @return FileQuery|\yii\db\ActiveQuery
//     */
//    public function getProductFeatures()
//    {
//        return $this->hasMany(ProductFeature::class, ['product_id'=>'id']);
//    }

    /**
     * @return UserQuery|\yii\db\ActiveQuery
     */
    public function getUpdated()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by_user_id']);
    }

    /***********************************************************************
     * Events.
     ***********************************************************************/

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->attachBehaviors([
            'TimestampBehavior' => 'davidhirtz\yii2\skeleton\behaviors\TimestampBehavior',
            'BlameableBehavior' => 'davidhirtz\yii2\skeleton\behaviors\BlameableBehavior',
        ]);

        if (!$this->type) {
            $this->type = static::TYPE_DEFAULT;
        }

        return parent::beforeSave($insert);
    }

    /***********************************************************************
     * Methods.
     ***********************************************************************/

//    /**
//     * @param Feature[] $features
//     */
//    public function populateFeaturesRelation($features)
//    {
//        if($features)
//        {
//            $relations=[];
//
//            foreach($this->productFeatures as $productFeature)
//            {
//                if(isset($features[$productFeature->feature_id]))
//                {
//                    $feature=$features[$productFeature->feature_id];
//
//                    $productFeature->populateFeatureRelation($feature);
//                    $productFeature->populateOptionRelation($productFeature->option_id && $feature->option_count ? ArrayHelper::getValue($feature->options, $productFeature->option_id) : null);
//
//                    $relations[$productFeature->feature_id]=$feature;
//                }
//            }
//
//            $this->populateRelation('features', $relations);
//        }
//    }

    /**
     * @inheritdoc
     * @return ProductQuery
     */
    public static function find()
    {
        return new ProductQuery(get_called_class());
    }

    /**
     * @param int $pimId
     * @return ProductQuery
     */
    public static function findByPimId($pimId)
    {
        return static::find()->where([static::tableName() . '.[[pim_id]]' => $pimId]);
    }

    /**
     * @param string $sku
     * @return ProductQuery
     */
    public static function findBySku($sku)
    {
        return static::find()->where([static::tableName() . '.[[sku]]' => $sku]);
    }

//    /**
//     * Recalculates feature count.
//     * @see updateFiles()
//     */
//    public function recalculateFeatureCount()
//    {
//        $this->feature_count=$this->getProductFeatures()->count();
//        $this->update(false, ['feature_count', 'updated_at']);
//    }

    /***********************************************************************
     * Getters / setters.
     ***********************************************************************/

//    /**
//     * @return string
//     */
//    public function getCartPictureSrc()
//    {
//    }

//    /**
//     * @return array
//     */
//    public function getCartAttributes()
//    {
//        return [
//            'name'=>$this->getI18nAttribute('name'),
//            'price'=>(float)$this->price,
//            'image'=>$this->getCartPictureSrc(),
//            'url'=>Url::to($this->getRoute()),
//        ];
//    }

//    /**
//     * @return string
//     */
//    public function getGatewayAdminUrl()
//    {
//        $gateway=static::getModule()->getGateway();
//        return rtrim($gateway->getAdminUri(), '/')."/catalog/product/edit/id/{$this->pim_id}/";
//    }


    /**
     * @return array
     */
    public static function getInventoryStatuses(): array
    {
        return [
            static::INVENTORY_STATUS_IN_STOCK => [
                'name' => Yii::t('shop', 'In Stock'),
            ],
            static::INVENTORY_STATUS_SOLD_OUT => [
                'name' => Yii::t('shop', 'Sold out'),
            ],
        ];
    }

    public static function getInventorySchemas(): array
    {
        return [
            static::INVENTORY_STATUS_IN_STOCK => 'http://schema.org/InStock',
            static::INVENTORY_STATUS_SOLD_OUT => 'http://schema.org/SoldOut',
        ];
    }

    /**
     * @return string
     */
    public function getInventoryStatusName(): string
    {
        $statuses = static::getInventoryStatuses();
        return isset($statuses[$this->inventory_status]) ? $statuses[$this->inventory_status] : '';
    }

    /**
     * @return string
     */
    public function getInventoryStatusSchema(): string
    {
        $schema = static::getInventorySchemas();
        return isset($schema[$this->inventory_status]) ? $schema[$this->inventory_status] : '';
    }

    /***********************************************************************
     * Active Record.
     ***********************************************************************/

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'sku' => Yii::t('shop', 'SKU'),
            'price' => Yii::t('shop', 'Price'),
            'compare_at_price' => Yii::t('shop', 'Compare at price'),
            'weight' => Yii::t('shop', 'Weight'),
            'description' => Yii::t('shop', 'Description'),
            'quantity' => Yii::t('shop', 'Quantity'),
            'inventory_status' => Yii::t('shop', 'Inventory'),
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