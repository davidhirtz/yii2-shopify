<?php

namespace davidhirtz\yii2\shopify\models;

use DateTime;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use Yii;
use yii\base\Model;

class Webhook extends Model
{
    use ModuleTrait;

    public const AUTH_WEBHOOK_UPDATE = 'shopifyWebhookUpdate';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $address;

    /**
     * @var array
     */
    public $route = [];

    /**
     * @var string
     */
    public $topic;

    /**
     * @var string
     */
    public $format;

    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var array
     */
    public $metafield_namespaces = [];

    /**
     * @var array
     */
    public $private_metafield_namespaces = [];

    /**
     * @var string
     */
    public $api_version;

    /**
     * @var DateTime
     */
    public $updated_at;

    /**
     * @var DateTime
     */
    public $created_at;

    /**
     * @return array
     */
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
                function ($attribute) {
                    if (!is_array($this->$attribute)) {
                        $this->addError($attribute, Yii::t('yii', 'The format of {attribute} is invalid.', [
                            'attribute' => $attribute,
                        ]));
                    }
                }
            ],
        ];
    }

    public function beforeValidate()
    {
        $this->address = $this->address ?: Yii::$app->getUrlManager()->createAbsoluteUrl($this->route);
        $this->api_version = $this->api_version ?: static::getModule()->shopifyApiVersion;

        return parent::beforeValidate();
    }

    /**
     * @return bool
     */
    public function create(): bool
    {
        if ($this->validate()) {
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
        }

        return false;
    }

    /**
     * @return string
     */
    public function getFormattedTopic()
    {
        return static::getTopics()[$this->topic] ?? ucfirst(str_replace('/', ' ', $this->topic));
    }

    /**
     * @return array
     */
    public static function getTopics()
    {
        return [
            'products/create' => Yii::t('shopify', 'Product created'),
            'products/update' => Yii::t('shopify', 'Product updated'),
            'products/delete' => Yii::t('shopify', 'Product deleted'),
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'address' => Yii::t('shopify', 'URL'),
            'topic' => Yii::t('shopify', 'Event'),
            'format' => Yii::t('shopify', 'Format'),
            'api_version' => Yii::t('shopify', 'API Version'),
            'updated_at' => Yii::t('skeleton', 'Last Update'),
        ];
    }
}