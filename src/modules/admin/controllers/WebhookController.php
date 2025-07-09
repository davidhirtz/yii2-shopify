<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionIterator;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMapper;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionCreateRequest;
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

    #[\Override]
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
                            'create',
                            'delete',
                            'index',
                        ],
                        'roles' => [WebhookSubscription::AUTH_WEBHOOK_UPDATE],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
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

    public function actionCreate(): Response|string
    {
        $urlManager = Yii::$app->getUrlManager();

        foreach (static::getModule()->webhooks as $attributes) {
            $url = 'https://www.davidhirtz.com/test'; //$urlManager->createAbsoluteUrl($attributes['route']);
            $request = new WebhookSubscriptionCreateRequest($attributes['topic'], $url);
            $request->execute();

            $errors = $request->getErrors();

            if (in_array('Address for this topic has already been taken', $errors)) {
                continue;
            }

            $this->errorOrSuccess($request->getErrors(), Yii::t('shopify', "The webhook \"{topic}\" was created.", [
                'topic' => $attributes['topic'],
            ]));
        }

        $this->error(Yii::$app->get('shopify')->getAdminApi()->getErrors());

        return $this->redirect(['index']);
    }

    public function actionDelete(int $id): Response|string
    {
        $this->error(Yii::$app->get('shopify')->getAdminApi()->getErrors());
        return $this->redirect(['index']);
    }
}
