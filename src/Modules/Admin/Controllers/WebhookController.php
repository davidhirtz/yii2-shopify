<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Controllers;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Shopify\Components\Admin\WebhookSubscriptionMutation;
use Hirtz\Shopify\Components\ShopifyComponent;
use Hirtz\Shopify\Models\WebhookSubscription;
use Hirtz\Shopify\Modules\Admin\Data\WebhookSubscriptionArrayDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
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
            $this->error(Lang::t('shopify', 'WEBHOOK_SHOPIFY_ADMIN_API_SECRET_KEY_MUST'));
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

            $this->errorOrSuccess($request->getErrors(), Lang::t('shopify', 'WEBHOOK_FLASH_THE_WEBHOOK_WAS_CREATED', [
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
            $this->success(Lang::t('shopify', 'WEBHOOK_FLASH_THE_WEBHOOK_WAS_DELETED'));
        }

        $this->error($request->getErrors());

        return $this->redirect(['index']);
    }
}
