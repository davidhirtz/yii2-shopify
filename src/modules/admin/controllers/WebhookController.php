<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\controllers;

use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMutation;
use davidhirtz\yii2\shopify\components\ShopifyComponent;
use davidhirtz\yii2\shopify\models\WebhookSubscription;
use davidhirtz\yii2\shopify\modules\admin\data\WebhookSubscriptionArrayDataProvider;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class WebhookController extends Controller
{
    use ModuleTrait;

    protected ShopifyComponent $shopify;

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

    #[Override]
    public function init(): void
    {
        $this->shopify = Yii::$app->get('shopify');
        parent::init();
    }

    public function actionIndex(): Response|string
    {
        if (!$this->shopify->shopifyApiSecret) {
            $this->error(Yii::t('shopify', 'Shopify Admin API secret key must be set to use webhooks.'));
        }

        $provider = new WebhookSubscriptionArrayDataProvider([
            'sort' => [
                'attributes' => ['topic', 'api_version', 'updated_at'],
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ],
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $request = new WebhookSubscriptionMutation();
        $urlManager = Yii::$app->getUrlManager();

        foreach (static::getModule()->webhooks as $attributes) {
            $request->create($attributes['topic'], $urlManager->createAbsoluteUrl($attributes['route']));
            $errors = $request->getErrors();

            if (in_array('Address for this topic has already been taken', $errors)) {
                continue;
            }

            $this->errorOrSuccess($request->getErrors(), Yii::t('shopify', "The webhook \"{topic}\" was created.", [
                'topic' => $attributes['topic'],
            ]));
        }

        $this->error($request->getErrors());

        return $this->redirect(['index']);
    }

    public function actionDelete(int $id): Response|string
    {
        $request = new WebhookSubscriptionMutation();

        if ($request->delete($id)) {
            $this->success(Yii::t('shopify', 'The webhook was deleted.'));
        }

        $this->error($request->getErrors());

        return $this->redirect(['index']);
    }
}
