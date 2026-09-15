<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Controllers;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Test\TestCase;
use Hirtz\Shopify\Test\Traits\ShopifyFixtureTrait;
use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * The webhook routes are the bundle's only unauthenticated endpoints: the HMAC of the raw body is the whole of
 * their access control, and `products/delete` removes a record.
 */
class WebhookControllerTest extends TestCase
{
    use ShopifyFixtureTrait;

    private const string SECRET = 'api-secret';

    public function testAProductIsDeleted(): void
    {
        $product = $this->getProductFromFixture('product-1');

        $this->request(['id' => $product->id]);

        self::assertNull(Product::findOne($product->id));
    }

    public function testAnUnsignedRequestIsRefused(): void
    {
        $product = $this->getProductFromFixture('product-1');

        try {
            $this->request(['id' => $product->id], signature: '');
            self::fail('The request was accepted.');
        } catch (UnauthorizedHttpException) {
        }

        self::assertNotNull(Product::findOne($product->id));
    }

    public function testARequestSignedWithTheWrongSecretIsRefused(): void
    {
        $product = $this->getProductFromFixture('product-1');
        $body = json_encode(['id' => $product->id]);
        self::assertNotFalse($body);

        try {
            $this->request(
                ['id' => $product->id],
                signature: base64_encode(hash_hmac('sha256', $body, 'not-the-secret', true)),
            );

            self::fail('The request was accepted.');
        } catch (UnauthorizedHttpException) {
        }

        self::assertNotNull(Product::findOne($product->id));
    }

    /**
     * The signature covers the body, so a payload swapped for another product's must not validate.
     */
    public function testASignatureOfADifferentBodyIsRefused(): void
    {
        $product = $this->getProductFromFixture('product-1');
        $signed = json_encode(['id' => 99999]);
        self::assertNotFalse($signed);

        try {
            $this->request(
                ['id' => $product->id],
                signature: base64_encode(hash_hmac('sha256', $signed, self::SECRET, true)),
            );

            self::fail('The request was accepted.');
        } catch (UnauthorizedHttpException) {
        }

        self::assertNotNull(Product::findOne($product->id));
    }

    public function testAPayloadWithoutAProductDeletesNothing(): void
    {
        $countBefore = (int)Product::find()->count();

        $this->request([]);

        self::assertSame($countBefore, (int)Product::find()->count());
    }

    public function testDeletingAProductThatIsNotThereIsNotAnError(): void
    {
        $countBefore = (int)Product::find()->count();

        $this->request(['id' => 99999]);

        self::assertSame($countBefore, (int)Product::find()->count());
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(array $payload, string $action = 'products-delete', ?string $signature = null): void
    {
        $body = json_encode($payload);
        self::assertNotFalse($body);
        $signature ??= base64_encode(hash_hmac('sha256', $body, self::SECRET, true));

        $request = $this->getWebRequest();
        $request->setRawBody($body);
        $request->getHeaders()->set('X-Shopify-Hmac-Sha256', $signature);

        $_SERVER['REQUEST_METHOD'] = 'POST';

        Yii::$app->runAction("shopify/webhook/$action");
    }
}
