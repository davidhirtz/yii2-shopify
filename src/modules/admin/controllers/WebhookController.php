<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionIterator;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMapper;
use davidhirtz\yii2\shopify\models\WebhookSubscription;
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
                        'actions' => [
                            'delete',
                            'index',
                            'update-all',
                        ],
                        'roles' => [WebhookSubscription::AUTH_WEBHOOK_UPDATE],
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
        if (!Yii::$app->get('shopify')->shopifyApiSecret) {
            $this->error(Yii::t('shopify', 'Shopify Admin API secret key must be set to use webhooks.'));
            return $this->redirect(['product/index']);
        }

        $webhooks = [];

        foreach ((new WebhookSubscriptionIterator(250)) as $data) {
            $webhooks[] = (new WebhookSubscriptionMapper($data['node']))();
        }

        usort($webhooks, fn (WebhookSubscription $a, WebhookSubscription $b) => strcmp((string)$b->updated_at, (string)$a->updated_at));

        return $this->render('index', [
            'webhooks' => $webhooks,
        ]);
    }

    public function actionUpdateAll(): Response|string
    {
        foreach (static::getModule()->webhooks as $attributes) {
            $webhook = Yii::createObject(WebhookSubscription::class);
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
