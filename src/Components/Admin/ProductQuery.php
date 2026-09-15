<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Components\GraphqlParser;
use Yii;

readonly class ProductQuery
{
    use ComponentTrait;
    public function __construct(private int $id)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        $query = (new GraphqlParser())->load('ProductQuery');

        $data = static::getShopify()->getAdminApi()->query($query, [
            'id' => "gid://shopify/Product/$this->id",
        ]);

        return $data['product'] ?? [];
    }
}
