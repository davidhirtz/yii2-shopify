<?php

declare(strict_types=1);

namespace Hirtz\Shopify;

use Hirtz\Shopify\Commands\ShopifyController;
use Hirtz\Shopify\Components\ShopifyComponent;
use Hirtz\Shopify\Controllers\WebhookController;
use Hirtz\Skeleton\Base\ConfigBootstrapInterface;
use Hirtz\Skeleton\Console\Application as ConsoleApplication;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Web\Application;
use Override;
use Yii;
use yii\i18n\PhpMessageSource;

class Bootstrap implements ConfigBootstrapInterface
{
    #[Override]
    public static function getDefaultConfig(): array
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'shopify' => [
                            'class' => PhpMessageSource::class,
                            'basePath' => '@shopify/../messages',
                            'forceTranslation' => true,
                        ],
                    ],
                ],
                'shopify' => [
                    'class' => ShopifyComponent::class,
                ],
            ],
            'modules' => [
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
            ],
        ];
    }

    /**
     * @param Application<User>|ConsoleApplication $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@shopify', __DIR__);

        // Not `getIsConsoleRequest()`: it falls back to `PHP_SAPI`, so a web application under the CLI SAPI —
        // every test run — would map the console controller over the `shopify` module and hide its routes.
        if ($app instanceof ConsoleApplication) {
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
