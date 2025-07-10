<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use Yii;

readonly class ProductQuery
{
    public function __construct(private int $id)
    {
    }

    public function __invoke(): array
    {
        $query = (new GraphqlParser())->load('ProductQuery');

        $data = Yii::$app->get('shopify')->getAdminApi()->query($query, [
            'id' => "gid://shopify/Product/$this->id",
        ]);

        return $data['product'] ?? [];
    }
}
