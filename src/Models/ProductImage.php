<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Skeleton\I18n\Lang;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use Hirtz\Shopify\Models\Traits\ProductRelationTrait;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Behaviors\TimestampBehavior;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Hirtz\Skeleton\Validators\RelationValidator;
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
class ProductImage extends ActiveRecord implements TrailModelInterface
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use TrailModelTrait;
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

    #[\Override]
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
            return Lang::t('skeleton', 'COMMON_MODEL_ID', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Lang::t('shopify', 'COMMON_IMAGE');
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
            'position' => Lang::t('shopify', 'PRODUCT_IMAGE_POSITION_LABEL'),
            'product_id' => Lang::t('shopify', 'PRODUCT_IMAGE_PRODUCT_ID_LABEL'),
            'alt_text' => Lang::t('shopify', 'PRODUCT_IMAGE_ALT_TEXT_LABEL'),
            'weight' => Lang::t('shopify', 'PRODUCT_IMAGE_WEIGHT_LABEL'),
            'height' => Lang::t('shopify', 'PRODUCT_IMAGE_HEIGHT_LABEL'),
            'src' => Lang::t('shopify', 'PRODUCT_IMAGE_SRC_LABEL'),
        ];
    }

    #[Override]
    public function formName(): string
    {
        return 'ProductImage';
    }

    #[Override]
    public static function tableName(): string
    {
        return '{{%product_image}}';
    }
}
