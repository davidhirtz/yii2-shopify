<?php

namespace davidhirtz\yii2\shopify;

use davidhirtz\yii2\shopify\controllers\WebhookController;
use davidhirtz\yii2\skeleton\web\Application;
use Yii;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@shopify', __DIR__);

        $app->extendComponent('i18n', [
            'translations' => [
                'shopify' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@shopify/messages',
                ],
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'shopify' => [
                        'class' => modules\admin\Module::class,
                    ],
                ],
            ],
            'shopify' => [
                'class' => Module::class,
            ],
        ]);

        /**
         * @see WebhookController::actionProductsCreate()
         * @see WebhookController::actionProductsDelete()
         * @see WebhookController::actionProductsUpdate()
         */
        $app->addUrlManagerRules(['shopify/webhook/<action>' => 'shopify/webhook/<action>']);
        $app->setMigrationNamespace('davidhirtz\yii2\shopify\migrations');
    }
}
