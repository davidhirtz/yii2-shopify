<?php

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\models\Webhook;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Class WebhookController
 * @package davidhirtz\yii2\shopify\modules\admin\controllers
 */
class WebhookController extends Controller
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
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
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        if (!static::getModule()->shopifyApiSecret) {
            $this->error(Yii::t('shopify', 'Shopify Admin REST API secret must be set to use webhooks.'));
        }

        $webhooks = [];

        foreach (static::getModule()->getApi()->getWebhooks() as $data) {
            $webhooks[] = new Webhook($data);
        }

        usort($webhooks, fn(Webhook $a, Webhook $b) => strcmp($b->updated_at, $a->updated_at));

        return $this->render('index', [
            'webhooks' => $webhooks,
        ]);
    }

    /**
     * @return Response
     */
    public function actionUpdateAll()
    {
        if (!static::getModule()->shopifyApiSecret) {
            throw new InvalidConfigException('Shopify Admin REST API secret must be set to use webhooks. Either via "Module::$shopifyApiKey" or via "shopifyApiKey" param.');
        }

        Yii::$app->getRequest()->setHostInfo('https://1a11-2001-a61-2b39-e001-101e-988d-78e6-f5fe.eu.ngrok.io/');

        foreach (static::getModule()->webhooks as $attributes) {
            $webhook = new Webhook();
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

    /**
     * @return Response
     */
    public function actionDelete($id)
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