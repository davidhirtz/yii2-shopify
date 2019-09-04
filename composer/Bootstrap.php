<?php

namespace davidhirtz\yii2\shop\composer;

use davidhirtz\yii2\skeleton\composer\BootstrapTrait;
use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\shop\bootstrap
 */
class Bootstrap implements BootstrapInterface
{
    use BootstrapTrait;

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@shop', dirname(__DIR__));

        $this->extendComponent($app, 'i18n', [
            'translations' => [
                'shop' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@shop/messages',
                ],
            ],
        ]);

        $this->extendModules($app, [
            'admin' => [
                'modules' => [
                    'shop' => [
                        'class' => 'davidhirtz\yii2\shop\modules\admin\Module',
                    ],
                ],
            ],
            'shop' => [
                'class' => 'davidhirtz\yii2\shop\Module',
            ],
            'media' => [
                'relations' => [
                    'davidhirtz\yii2\shop\models\Asset',
                ],
            ],
        ]);

        $this->setMigrationNamespace($app, 'davidhirtz\yii2\media\migrations');
    }
}