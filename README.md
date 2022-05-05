README
============================

Simple Shopify backend based on the [Yii 2](http://www.yiiframework.com/) extension [yii2-skeleton](https://github.com/davidhirtz/yii2-skeleton/) by David Hirtz.

CONFIGURATION
-------------

### Shopify setup

First, a custom app needs to be created in the Shopify admin under `Apps` > `Develop apps` > `Create an app`.
Once completed, the app needs to be configured to allow at least these Admin API access scopes:

- `read_inventory`
- `read_products`

### Credentials

Following Shopify credentials need to be either added to `config/params.php` or directly set as properties in `davidhirtz\yii2\shopify\Module`.
They can be found under `API credentials` in your private app settings in the Shopify admin.

    shopifyApiKey           API key (API key and secret key)
    shopifyApiSecret        API secret (API key and secret key)
    shopifyAccessToken      Admin API access token (IMPORTANT: Can only be accessed once!)
    shopDomain              Your shop URL (example: YOUR_NAME.myshopify.com)
