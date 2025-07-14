<?php

/**
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\commands;

use davidhirtz\yii2\shopify\components\admin\AdminApi;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionBatchQuery;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMapper;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMutation;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class ShopifyController extends Controller
{
    private AdminApi $api;

    #[\Override]
    public function init(): void
    {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
        parent::init();
    }

    #[\Override]
    public function afterAction($action, $result)
    {
        foreach ($this->api->getErrors() as $error) {
            $this->stderr("$error\n", Console::FG_RED);
        }

        return parent::afterAction($action, $result);
    }

    /**
     * Lists all webhook subscriptions.
     */
    public function actionWebhook(): void
    {
        foreach (new WebhookSubscriptionBatchQuery(250) as $data) {
            $webhook = (new WebhookSubscriptionMapper($data['node']))();
            $this->stdout(" > $webhook->id – $webhook->topic – $webhook->callbackUrl\n", Console::FG_YELLOW);
        }
    }

    /**
     * Creates a webhook subscription for the given topic and callback URL.
     */
    public function actionWebhookCreate(string $topic, string $callbackUrl): void
    {
        $request = new WebhookSubscriptionMutation();

        if ($request->create($topic, $callbackUrl)) {
            $this->stdout("Webhook subscription for topic '$topic' created successfully.\n", Console::FG_GREEN);
        }

        foreach ($request->getErrors() as $error) {
            $this->stderr("$error\n", Console::FG_RED);
        }
    }

    /**
     * Deletes a webhook subscription by its ID.
     */
    public function actionWebhookDelete(int $id): void
    {
        $request = new WebhookSubscriptionMutation();

        if ($request->delete($id)) {
            $this->stdout("Webhook subscription with ID '$id' deleted successfully.\n", Console::FG_GREEN);
        }

        foreach ($request->getErrors() as $error) {
            $this->stderr("$error\n", Console::FG_RED);
        }
    }
}
