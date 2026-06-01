<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\GraphqlParser;
use Yii;

readonly class StorefrontAccessTokenCreate
{
    public function __construct(private string $title)
    {
    }

    public function __invoke(): ?string
    {
        $query = (new GraphqlParser())->load('StorefrontAccessTokenCreate');

        $data = Yii::$app->get('shopify')->getAdminApi()->query($query, [
            'input' => [
                'title' => $this->title,
            ],
        ]);

        return $data['storefrontAccessTokenCreate']['storefrontAccessToken']['accessToken'] ?? null;
    }
}
