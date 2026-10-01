<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Components\Admin;

use Hirtz\Shopify\Components\ComponentTrait;
use Hirtz\Shopify\Components\GraphqlParser;

/**
 * Creates a Storefront API access token, which needs the app's `unauthenticated_*` access scopes.
 */
class StorefrontAccessTokenMutation
{
    use ComponentTrait;

    private readonly AdminApi $api;

    /**
     * @var list<string>
     */
    private array $errors = [];

    public function __construct()
    {
        $this->api = static::getShopify()->getAdminApi();
    }

    public function create(string $title): ?string
    {
        $query = (new GraphqlParser())->load('StorefrontAccessTokenCreate');

        $result = $this->api->query($query, [
            'input' => [
                'title' => $title,
            ],
        ]);

        $data = $result['storefrontAccessTokenCreate'] ?? [];

        foreach ($data['userErrors'] ?? [] as $error) {
            $this->errors[] = $error['message'] ?? 'Unknown error';
        }

        $token = $data['storefrontAccessToken']['accessToken'] ?? null;
        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
