<?php

namespace davidhirtz\yii2\shopify\composer;

use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\shopify\bootstrap
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@shopify', dirname(__DIR__));

        $app->extendComponent('i18n', [
            'translations' => [
                'shopify' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@shopify/messages',
                ],
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'shopify' => [
                        'class' => 'davidhirtz\yii2\shopify\modules\admin\Module',
                    ],
                ],
            ],
            'shopify' => [
                'class' => 'davidhirtz\yii2\shopify\Module',
            ],
        ]);

        $app->setMigrationNamespace('davidhirtz\yii2\shopify\migrations');
    }
}