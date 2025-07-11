<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;

class ProductMediaBatchQuery extends BatchQuery
{
    public function __construct(private readonly int $productId, int $batchSize = 250, ?string $cursor = null)
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

        return $data['product']['media']['edges'] ?? [];
    }

    protected function getQuery(): string
    {
        return (new GraphqlParser())->load('ProductMediaQuery');
    }
}
