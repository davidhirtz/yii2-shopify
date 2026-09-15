<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Base\Traits\ModelTrait;
use Override;
use Yii;
use yii\base\Model;

class Webhook extends Model
{
    use ComponentTrait;
    use ModelTrait;
    use ModuleTrait;

    public const string AUTH_SHOPIFY_WEBHOOK = 'shopifyWebhook';

    public ?int $id = null;
    public ?string $address = null;
    /**
     * @var array<int|string, mixed>
     */
    public array $route = [];
    public ?string $topic = null;
    public ?string $format = null;
    /**
     * @var list<string>
     */
    public array $fields = [];
    /**
     * @var list<string>
     */
    public array $metafield_namespaces = [];
    /**
     * @var list<string>
     */
    public array $private_metafield_namespaces = [];
    public ?string $api_version = null;
    public ?string $updated_at = null;
    public ?string $created_at = null;

    #[Override]
    public function rules(): array
    {
        return [
            [
                ['address', 'topic', 'api_version'],
                'required',
            ],
            [
                ['format'],
                'in',
                'range' => ['json', 'xml'],
            ],
            [
                ['route', 'fields', 'metafield_namespaces', 'private_metafield_namespaces'],
                function ($attribute): void {
                    if (!is_array($this->$attribute)) {
                        $this->addError($attribute, Yii::t('yii', 'The format of {attribute} is invalid.', [
                            'attribute' => $attribute,
                        ]));
                    }
                }
            ],
        ];
    }

    #[Override]
    public function beforeValidate(): bool
    {
        $this->address = $this->address ?: Yii::$app->getUrlManager()->createAbsoluteUrl($this->route);
        $this->api_version = $this->api_version ?: static::getShopify()->shopifyApiVersion;

        return parent::beforeValidate();
    }

    public function getFormattedTopic(): string
    {
        return static::getTopics()[$this->topic] ?? ucfirst(str_replace('/', ' ', $this->topic));
    }

    /**
     * @return array<string, string>
     */
    public static function getTopics(): array
    {
        return [
            'products/create' => Yii::t('shopify', 'WEBHOOK_PRODUCT_CREATED'),
            'products/update' => Yii::t('shopify', 'WEBHOOK_PRODUCT_UPDATED'),
            'products/delete' => Yii::t('shopify', 'WEBHOOK_PRODUCT_DELETED'),
        ];
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            'address' => Yii::t('shopify', 'WEBHOOK_ADDRESS_LABEL'),
            'topic' => Yii::t('shopify', 'WEBHOOK_TOPIC_LABEL'),
            'format' => Yii::t('shopify', 'WEBHOOK_FORMAT_LABEL'),
            'api_version' => Yii::t('shopify', 'WEBHOOK_API_VERSION_LABEL'),
            'updated_at' => Yii::t('skeleton', 'WEBHOOK_UPDATED_AT_LABEL'),
        ];
    }
}
