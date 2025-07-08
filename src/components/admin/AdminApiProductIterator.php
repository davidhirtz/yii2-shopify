<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

class AdminApiProductIterator extends AbstractAdminApiIterator
{
    protected function fetchData(): array
    {
        return $this->api->fetchProducts($this->batchSize, $this->currentCursor);
    }
}
