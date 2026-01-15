<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

use Hirtz\Shopify\Commands\ShopifyController;
use Hirtz\Shopify\Components\ShopifyComponent;
use Hirtz\Shopify\controllers\WebhookController;
use Hirtz\Skeleton\Web\Application;
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

        $app->getI18n()->translations['shopify'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => '@shopify/../messages',
        ];

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'shopify' => [
                        'class' => Modules\Admin\Module::class,
                    ],
                ],
            ],
            'shopify' => [
                'class' => Module::class,
            ],
        ]);

        $app->extendComponent('shopify', [
            'class' => ShopifyComponent::class,
        ]);

        if ($app->getRequest()->getIsConsoleRequest()) {
            $app->controllerMap['shopify'] ??= ShopifyController::class;
        }

        /**
         * @see WebhookController::actionProductsCreate()
         * @see WebhookController::actionProductsDelete()
         * @see WebhookController::actionProductsUpdate()
         */
        $app->addUrlManagerRules(['shopify/webhook/<action>' => 'shopify/webhook/<action>']);
        $app->setMigrationNamespace('Hirtz\Shopify\Migrations');
    }
}
