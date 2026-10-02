<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Components\GraphqlParser;

readonly class ProductQuery
{
    use ComponentTrait;
    public function __construct(private int $id)
    {
    }

    /**
     * An empty array is a product Shopify no longer has, `null` a request that failed: only the first may be read
     * as a deletion.
     *
     * @return array<string, mixed>|null
     */
    public function __invoke(): ?array
    {
        $query = (new GraphqlParser())->load('ProductQuery');
        $api = static::getShopify()->getAdminApi();
        $errorCount = count($api->getErrors());

        $data = $api->query($query, [
            'id' => "gid://shopify/Product/$this->id",
        ]);

        if (count($api->getErrors()) > $errorCount) {
            return null;
        }

        return $data['product'] ?? [];
    }
}
