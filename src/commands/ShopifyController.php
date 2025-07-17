<?php

/**
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\commands;

use davidhirtz\yii2\shopify\components\admin\AdminApi;
use davidhirtz\yii2\shopify\components\admin\ProductBatchRepository;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionBatchQuery;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMapper;
use davidhirtz\yii2\shopify\components\admin\WebhookSubscriptionMutation;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\console\controllers\traits\ControllerTrait;
use Override;
use Yii;
use yii\base\Event;
use yii\console\Controller;
use yii\db\AfterSaveEvent;
use yii\helpers\Console;

class ShopifyController extends Controller
{
    use ControllerTrait;

    private AdminApi $api;

    #[Override]
    public function init(): void
    {
        $this->api = Yii::$app->get('shopify')->getAdminApi();
        parent::init();
    }

    #[Override]
    public function afterAction($action, $result)
    {
        foreach ($this->api->getErrors() as $error) {
            $this->stderr("$error\n", Console::FG_RED);
        }

        return parent::afterAction($action, $result);
    }

    /**
     * Updates all products in the store.
     */
    public function actionImport(): void
    {
        $this->interactiveStartStdout("Importing products...");

        $insertedCount = 0;
        $deletedCount = 0;
        $updatedCount = 0;

        Event::on(Product::class, Product::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) use (&$updatedCount) {
            if (count($event->changedAttributes) > 2) {
                $updatedCount++;
            }
        });

        Event::on(Product::class, Product::EVENT_AFTER_INSERT, function () use (&$insertedCount) {
            $insertedCount++;
        });

        Event::on(Product::class, Product::EVENT_AFTER_DELETE, function () use (&$deletedCount) {
            $deletedCount++;
        });

        $repository = new ProductBatchRepository();
        $repository->save();

        $this->interactiveDoneStdout();

        $formatter = Yii::$app->getFormatter();

        if ($insertedCount || $updatedCount || $deletedCount) {
            $inserted = $insertedCount > 0 ? $formatter->asInteger($insertedCount) : 'None';
            $updated = $updatedCount > 0 ? $formatter->asInteger($updatedCount) : 'none';
            $deleted = $deletedCount > 0 ? $formatter->asInteger($deletedCount) : 'none';
            $text = "$inserted added, $updated updated, $deleted deleted.";
        } else {
            $text = 'No products were added, updated or deleted.';
        }

        $this->stdout($text . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Lists all active webhook subscriptions.
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
