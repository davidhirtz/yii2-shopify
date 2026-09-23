# yii2-shopify

Mirrors the products of a Shopify store into the database of a [yii2-skeleton](https://github.com/davidhirtz/yii2-skeleton)
application and keeps them in sync through webhooks, so a site can render products, variants and images without
calling Shopify on every request. It talks to the Shopify Admin GraphQL API, adds a *Products* section to the
admin and depends on `davidhirtz/yii2-skeleton` alone. It writes nothing back to Shopify.

## Installation

```bash
composer require davidhirtz/yii2-shopify
./yii migrate
```

The bundle bootstraps itself through `extra.bootstrap` (`Hirtz\Shopify\Bootstrap`): it registers the `shopify`
module, the `admin/shopify` submodule, the `shopify` component, the `shopify` console command, the
`shopify/webhook/<action>` URL rule and its migration namespace. The migration creates `product`,
`product_variant` and `product_image` plus the permissions `shopifyProduct` and `shopifyWebhook`, both granted
to `admin` and `manager`.

## Configuration

### Module

`modules.shopify` reads one property:

| Property   | Default                                                                  | Meaning                                                                           |
|------------|--------------------------------------------------------------------------|-----------------------------------------------------------------------------------|
| `webhooks` | `PRODUCTS_CREATE`, `PRODUCTS_UPDATE`, `PRODUCTS_DELETE` → `shopify/webhook/products-*` | The subscriptions *Install Webhooks* registers, as `['topic' => …, 'route' => […]]` |

### Component and params

`components.shopify` is `Components\ShopifyComponent`. Every credential falls back to the `params` key of the
same name, which is where a project normally keeps them:

| Property / param                | Default            | Meaning                                                                        |
|---------------------------------|--------------------|--------------------------------------------------------------------------------|
| `shopifyShopName`               | —                  | The `NAME` in `https://NAME.myshopify.com`; required for every API call        |
| `shopifyAccessToken`            | —                  | Admin API access token; required for every API call                            |
| `shopifyApiSecret`              | —                  | App secret; required to validate the HMAC of incoming webhooks                 |
| `shopifyShopDomain`             | `NAME.myshopify.com` | Custom shop domain, used for the links into the Shopify admin                |
| `shopifyApiKey`                 | —                  | Stored for the project's own use, not read by the bundle                       |
| `shopifyStorefrontAccessToken`  | —                  | Stored for the project's frontend (Storefront API), not read by the bundle     |
| `shopifyApiVersion`             | `2025-07`          | Admin API version the requests and the webhook subscriptions name              |
| `defaultCurrency`               | `EUR`              | Currency `ProductVariant::getFormattedPrice()` formats with                    |

A project that declares `components.shopify` itself must name the class, or the application refuses the
definition before the bootstrap can supply it:

```php
'components' => [
    'shopify' => [
        'class' => \Hirtz\Shopify\Components\ShopifyComponent::class,
        'shopifyApiVersion' => '2025-10',
    ],
],
'params' => [
    'shopifyShopName' => 'my-shop',
    'shopifyAccessToken' => 'shpat_…',
    'shopifyApiSecret' => '…',
],
```

### Container

`Models\Product`, `Models\ProductImage` and `Models\ProductVariant` translate through the skeleton's
`translation` table. Which attributes are translated is a container definition:

```php
'container' => [
    'definitions' => [
        \Hirtz\Shopify\Models\Product::class => [
            'i18nAttributes' => ['name', 'content', 'slug'],
        ],
        \Hirtz\Shopify\Models\ProductImage::class => [
            'i18nAttributes' => ['alt_text'],
        ],
    ],
],
```

`Product::$htmlValidator` names the validator for the product description (`Hirtz\Skeleton\Validators\HtmlValidator`
by default); `null` disables it.

## Console commands

| Command                                        | Purpose                                                          |
|------------------------------------------------|------------------------------------------------------------------|
| `shopify/import`                               | Imports every product, inserting, updating and deleting as needed |
| `shopify/webhook`                              | Lists the active webhook subscriptions                           |
| `shopify/webhook-create <topic> <callbackUrl>` | Creates a webhook subscription                                   |
| `shopify/webhook-delete <id>`                  | Deletes a webhook subscription by its Shopify id                 |

## Shopify setup

Create a custom app in the Shopify admin under *Apps* › *Develop apps* and give it at least the Admin API access
scopes `read_inventory` and `read_products`. Its *API credentials* page holds the shop name, the API key and
secret, and the Admin API access token, which Shopify shows only once.

A project using the Storefront API from its own frontend also activates the Storefront API integration
(`unauthenticated_read_product_listings`, `unauthenticated_read_product_inventory`, and the checkout and
customer scopes it needs) and keeps that token under `shopifyStorefrontAccessToken`; the bundle itself never
calls the Storefront API.

## Webhooks

Products sync on change: Shopify posts to `shopify/webhook/products-create`, `products-update` and
`products-delete` (`Controllers\WebhookController`), which verify the `X-Shopify-Hmac-Sha256` header against
`shopifyApiSecret`, fetch the product through the Admin API and save it. The endpoints need a public URL and
CSRF validation is off for them.

Register the subscriptions from the admin under *Products* › *Webhooks* (permission `shopifyWebhook`) with
*Install Webhooks*, or with `shopify/webhook-create`. Products that existed before the subscriptions did, and a
sync that looks wrong, are fixed with *Reload Products* on the product index or `./yii shopify/import`.

## Products in the admin

*Products* (permission `shopifyProduct`) lists the mirrored products with their status, image, inventory and
variant count, links each into the Shopify admin and offers a per-product reload. The dashboard carries a link to
the Shopify admin. Editing happens in Shopify; the admin never writes a product.

## Shopify theme

To disable the *Online store* app in Shopify, create a minimal theme or upload the
[headless theme](https://github.com/instantcommerce/shopify-headless-theme). The Shopify *Thank you* page accepts
custom HTML under *Settings* › *Checkout* › *Additional scripts*, which is where a "back to the site" link goes:

```html
<a href="https://www.example.com/" target="_blank" class="btn" style="margin-top:30px">
{% if shop.locale == "de" %}
Zurück zum Shop
{% else %}
Return to shop
{% endif %}
</a>
```
