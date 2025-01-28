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
                ['id', 'product_id', 'position', 'width', 'height', 'src'],
                'required',
            ],
            [
                ['id', 'product_id'],
                'string',
            ],
            [
                ['position', 'width', 'height'],
                'number',
                'integerOnly' => true,
            ],
        ]);
    }

    public function getUrl(array $params = []): string
    {
        return $this->src . ($params ? ((strpos((string) $this->src, '?') ? '&' : '?') . http_build_query($params)) : '');
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

    public function formName(): string
    {
        return 'ProductImage';
    }

    public static function tableName(): string
    {
        return static::getModule()->getTableName('product_image');
    }
}
