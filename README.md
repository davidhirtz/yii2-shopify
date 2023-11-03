Shopify backend based on the [Yii 2](http://www.yiiframework.com/) extension [yii2-skeleton](https://github.com/davidhirtz/yii2-skeleton/).

### Shopify setup

First, a custom app needs to be created in the Shopify admin under `Apps` > `Develop apps` > `Create an app`. Once
completed, the app needs to be configured to allow at least these Admin API access scopes:

- `read_inventory`
- `read_products`

To use the Storefront API (shopify.js), activate the Storefront API integration with the following permissions:

- `unauthenticated_write_checkouts`
- `unauthenticated_read_checkouts`
- `unauthenticated_write_customers`
- `unauthenticated_read_customers`
- `unauthenticated_read_product_listings`
- `unauthenticated_read_product_inventory`

### Credentials

Following Shopify credentials need to be either added to `config/params.php` or directly set as properties in
`davidhirtz\yii2\shopify\Module`. They can be found under `API credentials` in your private app settings in the Shopify
admin.

    shopifyShopName                 The shopify name of your shop (https://NAME.myshopify.com/)
    shopifyShopDomain               Your optional custom shop URL
    shopifyApiKey                   API key (API key and secret key)
    shopifyApiSecret                API secret (API key and secret key)
    shopifyAccessToken              Admin API access token (IMPORTANT: Can only be accessed once!)
    shopifyStorefrontAccessToken    Storefront access token

### Webhooks

After the configuration is completed, go to `Products` > `Webhooks` (the user permission `Manage Shopify webhooks` is
required) and click on `Install Webhooks` to register the necessary webhooks which sync the Shopify admin with the website.

### Products

Products are synced automatically on change in Shopify admin. If products were already created before the webhooks were
registered or there seems to be an issue with the sync, click on `Reload Products` to load all products from Shopify.

### Shopify Theme

To disable the "Online shop" app in the backend, you can create a minimal theme or upload this [package](https://github.com/instantcommerce/shopify-headless-theme). Additionally, you can add custom code to the Shopify "Thank you" page to add trackers or a "back to website" button. The text field "Additional scripts" is located in your Shopify Backend at domain.myshopify.com/admin/settings/checkout. It accepts HTML, JS and the liquid objects `shop` and ` checkout`.

```html
<a href="https://www.domain.com/" target="_blank" class="btn" style="margin-top:30px">
{% if shop.locale == "de" %}
Zurück zum Shop
{% else %}
Return to shop
{% endif %}
</a>
```
