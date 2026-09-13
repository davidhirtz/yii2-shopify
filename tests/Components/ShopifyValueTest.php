<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components;

use Hirtz\Shopify\Components\ShopifyDateTime;
use Hirtz\Shopify\Components\ShopifyId;
use Hirtz\Shopify\Components\ShopifyPrice;
use Hirtz\Shopify\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Yii;
use yii\base\InvalidConfigException;

class ShopifyValueTest extends TestCase
{
    /**
     * A GraphQL id is a path, a webhook payload carries the bare number.
     */
    #[DataProvider('idDataProvider')]
    public function testTheIdIsTheLastSegment(string $id, int $expected): void
    {
        self::assertSame($expected, (new ShopifyId($id))->toInt());
    }

    public static function idDataProvider(): array
    {
        return [
            ['gid://shopify/Product/1234567890', 1234567890],
            ['gid://shopify/ProductVariant/42', 42],
            ['gid://shopify/MediaImage/7?foo=bar', 7],
            ['1234567890', 1234567890],
            ['', 0],
        ];
    }

    /**
     * The price is stored in cents, and multiplying a float by 100 and truncating loses one on most of them:
     * `(int)(19.99 * 100)` is 1998.
     */
    #[DataProvider('priceDataProvider')]
    public function testThePriceIsRoundedToTheCent(int|float|string $value, int $expected): void
    {
        self::assertSame($expected, (new ShopifyPrice($value))->toInt());
    }

    public static function priceDataProvider(): array
    {
        return [
            ['19.99', 1999],
            ['0.29', 29],
            ['1.15', 115],
            ['34.99', 3499],
            ['0.00', 0],
            ['1000000.99', 100000099],
            [19.99, 1999],
            [20, 2000],
        ];
    }

    public function testThePriceTakesTheCurrencyOfTheShop(): void
    {
        self::assertSame(1999, (new ShopifyPrice('19.99', 'USD'))->toInt());
        self::assertSame(1999, (new ShopifyPrice('19.99'))->toInt());
    }

    public function testTheDateTimeIsConvertedToTheApplicationTimeZone(): void
    {
        Yii::$app->setTimeZone('Europe/Berlin');

        $dateTime = (new ShopifyDateTime('2026-09-13T10:00:00Z'))->toDateTime();

        self::assertSame('Europe/Berlin', $dateTime->getTimezone()->getName());
        self::assertSame('2026-09-13 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testValidatingAWebhookNeedsTheApiSecret(): void
    {
        $shopify = Yii::$app->get('shopify');
        $data = '{"id":1}';

        $hmac = base64_encode(hash_hmac('sha256', $data, 'api-secret', true));

        self::assertTrue($shopify->validateHmac($hmac, $data));
        self::assertFalse($shopify->validateHmac($hmac, '{"id":2}'));
        self::assertFalse($shopify->validateHmac('', $data));
        self::assertFalse($shopify->validateHmac('not-the-signature', $data));

        $shopify->shopifyApiSecret = null;

        $this->expectException(InvalidConfigException::class);
        $shopify->validateHmac($hmac, $data);
    }
}
