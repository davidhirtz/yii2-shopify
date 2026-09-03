<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Shopify\Modules\ModuleTrait;
use Override;
use Yii;
use yii\base\Model;

class Webhook extends Model
{
    use ModuleTrait;

    public const string AUTH_WEBHOOK_UPDATE = 'shopifyWebhookUpdate';

    public ?int $id = null;
    public ?string $address = null;
    public array $route = [];
    public ?string $topic = null;
    public ?string $format = null;
    public array $fields = [];
    public array $metafield_namespaces = [];
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
        $this->api_version = $this->api_version ?: static::getModule()->shopifyApiVersion;

        return parent::beforeValidate();
    }

    public function create(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $params = array_filter([
            'topic' => $this->topic,
            'address' => $this->address,
            'format' => $this->format,
            'fields' => $this->fields,
            'metafield_namespaces' => $this->metafield_namespaces,
            'private_metafield_namespaces' => $this->private_metafield_namespaces,
        ]);

        $api = static::getModule()->getApi();

        if ($result = $api->setWebhook($params)) {
            $this->setAttributes($result, false);
            return true;
        }

        foreach ($api->getErrors() as $attribute => $errors) {
            if ($attribute !== 'address' && $errors !== ['for this topic has already been taken']) {
                foreach ($errors as $error) {
                    $this->addError($attribute, $error);
                }
            }
        }

        return false;
    }

    public function getFormattedTopic(): string
    {
        return static::getTopics()[$this->topic] ?? ucfirst(str_replace('/', ' ', $this->topic));
    }

    public static function getTopics(): array
    {
        return [
            'products/create' => Lang::t('shopify', 'WEBHOOK_PRODUCT_CREATED'),
            'products/update' => Lang::t('shopify', 'WEBHOOK_PRODUCT_UPDATED'),
            'products/delete' => Lang::t('shopify', 'WEBHOOK_PRODUCT_DELETED'),
        ];
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            'address' => Lang::t('shopify', 'WEBHOOK_ADDRESS_LABEL'),
            'topic' => Lang::t('shopify', 'WEBHOOK_TOPIC_LABEL'),
            'format' => Lang::t('shopify', 'WEBHOOK_FORMAT_LABEL'),
            'api_version' => Lang::t('shopify', 'WEBHOOK_API_VERSION_LABEL'),
            'updated_at' => Lang::t('skeleton', 'WEBHOOK_UPDATED_AT_LABEL'),
        ];
    }
}
