<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;

class ProductBatchQuery extends BatchQuery
{
    protected function fetchData(): array
    {
        $data = $this->api->query($this->getQuery(), [
            'limit' => $this->batchSize,
            'cursor' => $this->currentCursor,
        ]);

        return $data['products']['edges'] ?? [];
    }

    protected function getQuery(): string
    {
        return (new GraphqlParser())->load('ProductsQuery');
    }
}
