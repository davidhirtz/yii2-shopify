<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Tests\Components\Admin;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Components\Admin\AdminApi;
use Hirtz\Skeleton\Test\TestCase;
use Override;

/**
 * A failed request leaves its reason in `getErrors()`, whatever the body it came with.
 */
class AdminApiTest extends TestCase
{
    public function testTheDataOfASuccessfulQuery(): void
    {
        $api = $this->createApi(new Response(200, [], '{"data":{"shop":{"name":"Shop"}}}'));

        self::assertSame(['shop' => ['name' => 'Shop']], $api->query('{shop{name}}'));
        self::assertSame([], $api->getErrors());
    }

    public function testTheErrorsOfAJsonErrorBody(): void
    {
        $api = $this->createApi(new Response(401, [], '{"errors":"[API] Invalid API key or access token"}'));

        self::assertSame([], $api->query('{shop{name}}'));
        self::assertSame(['[API] Invalid API key or access token'], $api->getErrors());
    }

    public function testTheMessagesOfGraphqlErrorObjects(): void
    {
        $api = $this->createApi(new Response(400, [], '{"errors":[{"message":"Field does not exist"}]}'));

        $api->query('{nope}');

        self::assertSame(['Field does not exist'], $api->getErrors());
    }

    public function testAnHtmlErrorBodyFallsBackToTheExceptionMessage(): void
    {
        $api = $this->createApi(new Response(429, [], '<html><body>Too many requests</body></html>'));

        self::assertSame([], $api->query('{shop{name}}'));

        $errors = $api->getErrors();

        self::assertCount(1, $errors);
        self::assertStringContainsString('429', $errors[0]);
    }

    public function testAnEmptyServerErrorFallsBackToTheExceptionMessage(): void
    {
        $api = $this->createApi(new Response(503));

        $api->query('{shop{name}}');

        self::assertCount(1, $api->getErrors());
        self::assertStringContainsString('503', $api->getErrors()[0]);
    }

    private function createApi(Response $response): AdminApi
    {
        return new TestAdminApi(new MockHandler([$response]));
    }
}

class TestAdminApi extends AdminApi
{
    public function __construct(private readonly MockHandler $handler)
    {
        parent::__construct('shop-name', 'access-token', '2025-07');
    }

    #[Override]
    protected function createClient(): Client
    {
        return new Client(['handler' => HandlerStack::create($this->handler)]);
    }
}
