<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests;

use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Skeleton\Web\UrlManager;
use Yii;

class ModuleTest extends TestCase
{
    use ModuleTrait;

    public function testTheWebhookAddressCarriesNoLanguagePrefix(): void
    {
        Yii::$app->set('urlManager', [
            'class' => UrlManager::class,
            'i18nUrl' => true,
            'defaultLanguage' => 'en-US',
            'languages' => ['en-US' => 'en', 'de' => 'de'],
            'hostInfo' => 'https://www.test.localhost',
        ]);

        Yii::$app->language = 'de';
        $german = static::getModule()->getWebhookAddress(['/shopify/webhook/products-update']);

        Yii::$app->language = 'en-US';
        $english = static::getModule()->getWebhookAddress(['/shopify/webhook/products-update']);

        self::assertSame('https://www.test.localhost/shopify/webhook/products-update', $german);
        self::assertSame($english, $german);
    }
}
