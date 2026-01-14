<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Controllers;

use Hirtz\Shopify\Models\Webhook;
use Hirtz\Shopify\Modules\Admin\Data\WebhookArrayDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
use Override;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class WebhookController extends Controller
{
    use ModuleTrait;

    #[Override]
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

        $provider = Yii::createObject(WebhookArrayDataProvider::class);

        return $this->render('index', [
            'provider' => $provider,
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
