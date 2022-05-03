<?php

namespace davidhirtz\yii2\shopify\models\base;

use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\db\TypeAttributeTrait;
use davidhirtz\yii2\skeleton\models\queries\UserQuery;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class Product
 * @package davidhirtz\yii2\shopify\models\base
 * @see Product
 *
 * @property int $id
 * @property int $status
 * @property string $name
 * @property string $content
 * @property string $slug
 * @property string $vendor
 * @property int $variant_count
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property ProductVariants[] $variants
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
            [
                $this->getI18nAttributesNames(['name']),
                'filter',
                'filter' => 'trim',
            ],
            array_merge(
                [$this->getI18nAttributesNames(['content'])],
                (array)($this->contentType == 'html' && $this->htmlValidator ? $this->htmlValidator : 'safe')
            ),
        ]);
    }

    /**
     * @return UserQuery|ActiveQuery
     */
    public function getUpdated()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by_user_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->attachBehaviors([
            'BlameableBehavior' => 'davidhirtz\yii2\skeleton\behaviors\BlameableBehavior',
            'TimestampBehavior' => 'davidhirtz\yii2\skeleton\behaviors\TimestampBehavior',
        ]);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @return ProductQuery
     */
    public static function find()
    {
        return new ProductQuery(get_called_class());
    }

    /**
     * @return array
     */
    public static function getInventoryStatuses(): array
    {
        return [
            static::INVENTORY_STATUS_IN_STOCK => [
                'name' => Yii::t('shopify', 'In Stock'),
            ],
            static::INVENTORY_STATUS_SOLD_OUT => [
                'name' => Yii::t('shopify', 'Sold out'),
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
            'sku' => Yii::t('shopify', 'SKU'),
            'price' => Yii::t('shopify', 'Price'),
            'compare_at_price' => Yii::t('shopify', 'Compare at price'),
            'weight' => Yii::t('shopify', 'Weight'),
            'description' => Yii::t('shopify', 'Description'),
            'quantity' => Yii::t('shopify', 'Quantity'),
            'inventory_status' => Yii::t('shopify', 'Inventory'),
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