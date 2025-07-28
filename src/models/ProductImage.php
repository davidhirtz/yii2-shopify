<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\shopify\models\traits\ProductRelationTrait;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\behaviors\TimestampBehavior;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\models\traits\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\validators\RelationValidator;
use Override;
use Yii;

/**
 * @property int $id
 * @property int $product_id
 * @property int $position
 * @property string|null $alt_text
 * @property int|null $width
 * @property int|null $height
 * @property string|null $src
 * @property DateTime|null $updated_at
 * @property DateTime $created_at
 */
class ProductImage extends ActiveRecord
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
            'TimestampBehavior' => TimestampBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ];
    }

    #[Override]
    public function rules(): array
    {
        return $this->getI18nRules([
            [
                ['id', 'product_id', 'position', 'width', 'height', 'src'],
                'required',
            ],
            [
                ['product_id'],
                RelationValidator::class
            ],
            [
                ['width', 'height'],
                'number',
                'integerOnly' => true,
            ],
        ]);
    }

    public function beforeDelete(): bool
    {
        $product = $this->getProduct()
            ->andWhere(['image_id' => $this->id])
            ->one();

        if ($product) {
            $product->image_id = null;
            $product->update();
        }

        $variants = ProductVariant::find()
            ->where(['product_id' => $this->product_id, 'image_id' => $this->id])
            ->all();

        foreach ($variants as $variant) {
            $variant->image_id = null;
            $variant->update();
        }

        return parent::beforeDelete();
    }

    public function getUrl(array $params = []): string
    {
        return $this->src . ($params ? ((strpos((string)$this->src, '?') ? '&' : '?') . http_build_query($params)) : '');
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
        if ($this->id) {
            return Yii::t('skeleton', '{model} #{id}', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Yii::t('shopify', 'Image');
    }

    public function getTrailModelAdminRoute(): array|false
    {
        return $this->getAdminRoute();
    }

    public function getAdminRoute(): array|false
    {
        return ['/admin/product/update', 'id' => $this->product_id];
    }

    #[Override]
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'position' => Yii::t('shopify', 'Position'),
            'product_id' => Yii::t('shopify', 'Product'),
            'alt_text' => Yii::t('shopify', 'Alt text'),
            'weight' => Yii::t('shopify', 'Weight'),
            'height' => Yii::t('shopify', 'Height'),
            'src' => Yii::t('shopify', 'URL'),
        ]);
    }

    #[Override]
    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_image');
    }
}
