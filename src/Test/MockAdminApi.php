<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Hirtz\Shopify\Components\Admin\AdminApi;
use Override;

/**
 * Answers every request with the next of the given responses, set through `Yii::$container->set(AdminApi::class, …)`.
 */
class MockAdminApi extends AdminApi
{
    private readonly MockHandler $handler;

    public function __construct(Response|ConnectException ...$responses)
    {
        $this->handler = new MockHandler($responses);
        parent::__construct('shop-name', 'access-token', '2026-07');
    }

    #[Override]
    protected function createClient(): Client
    {
        return new Client(['handler' => HandlerStack::create($this->handler)]);
    }
}
