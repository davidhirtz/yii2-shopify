<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class WebhookController extends Controller
{
    use ModuleTrait;

    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['delete', 'index', 'update-all'],
                        'roles' => [Webhook::AUTH_WEBHOOK_UPDATE],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'update-all' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): Response|string
    {
        if (!static::getModule()->shopifyApiSecret) {
            $this->error(Yii::t('shopify', 'Shopify Admin API secret key must be set to use webhooks.'));
        }

        $webhooks = [];

        foreach (static::getModule()->getApi()->getWebhooks() as $data) {
            $webhooks[] = Yii::createObject(array_merge($data, [
                'class' => Webhook::class,
            ]));
        }

        usort($webhooks, fn (Webhook $a, Webhook $b) => strcmp((string) $b->updated_at, (string) $a->updated_at));

        return $this->render('index', [
            'webhooks' => $webhooks,
        ]);
    }

    public function actionUpdateAll(): Response|string
    {
        if (!static::getModule()->shopifyApiSecret) {
            throw new InvalidConfigException('Shopify Admin API secret key must be set to use webhooks. Either via "Module::$shopifyApiSecret" or via "shopifyApiSecret" param.');
        }

        foreach (static::getModule()->webhooks as $attributes) {
            $webhook = Yii::createObject(Webhook::class);
            $webhook->setAttributes($attributes);

            if ($webhook->create()) {
                $this->success(Yii::t('shopify', "The webhook \"{topic}\" was created.", [
                    'topic' => $webhook->getFormattedTopic(),
                ]));
            } elseif (!$webhook->getErrors()) {
                $this->success(Yii::t('shopify', "The webhook \"{topic}\" was skipped.", [
                    'topic' => $webhook->getFormattedTopic(),
                ]));
            } else {
                $this->error($webhook);
            }
        }

        return $this->redirect(['index']);
    }

    public function actionDelete(int $id): Response|string
    {
        $api = static::getModule()->getApi();

        if ($api->deleteWebhook($id)) {
            $this->success(Yii::t('shopify', 'The webhook was deleted.'));
        }

        if ($api->getErrors()) {
            $this->error($api->getErrors());
        }

        return $this->redirect(['index']);
    }
}
