<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\GraphqlParser;

class ProductVariantBatchQuery extends BatchQuery
{
    public function __construct(private readonly int $productId, int $batchSize = 2000, ?string $cursor = null)
    {
        parent::__construct($batchSize, $cursor);
    }

    protected function fetchData(): array
    {
        $data = $this->api->query($this->getQuery(), [
            'id' => "gid://shopify/Product/$this->productId",
            'limit' => $this->batchSize,
            'cursor' => $this->currentCursor,
        ]);

        return $data['product']['variants']['edges'] ?? [];
    }

    protected function getQuery(): string
    {
        return (new GraphqlParser())->load('ProductVariantsQuery');
    }
}
