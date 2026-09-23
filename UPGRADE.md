# Upgrading to 3.0

## Requirements

- PHP `^8.3`
- `davidhirtz/yii2-skeleton` `^3.0`, upgraded first (its `UPGRADE.md` covers the namespace rename, the
  `translation` table and the admin widget layer this bundle builds on)
- `moneyphp/money` `^4.7`, unchanged

```bash
composer require davidhirtz/yii2-shopify:^3.0
```

3.0 branches off 2.2.1. The two 2.3.x releases (automatic `shopifyAccessToken` generation, the
`shopify/storefront-access-token` command, API version `2026-04`) were not carried over, see [Removed](#removed).

## Renames

### Namespaces

Every directory is StudlyCase; the class names inside are unchanged unless listed below.

| v2                                                          | v3                                                      |
|-------------------------------------------------------------|---------------------------------------------------------|
| `davidhirtz\yii2\shopify\`                                  | `Hirtz\Shopify\`                                        |
| `davidhirtz\yii2\shopify\commands\`                         | `Hirtz\Shopify\Commands\`                               |
| `davidhirtz\yii2\shopify\components\`                       | `Hirtz\Shopify\Components\`                             |
| `davidhirtz\yii2\shopify\components\admin\`                 | `Hirtz\Shopify\Components\Admin\`                       |
| `davidhirtz\yii2\shopify\controllers\`                      | `Hirtz\Shopify\Controllers\`                            |
| `davidhirtz\yii2\shopify\migrations\`                       | `Hirtz\Shopify\Migrations\`                             |
| `davidhirtz\yii2\shopify\models\`                           | `Hirtz\Shopify\Models\`                                 |
| `davidhirtz\yii2\shopify\models\queries\`                   | `Hirtz\Shopify\Models\Queries\`                         |
| `davidhirtz\yii2\shopify\models\traits\`                    | `Hirtz\Shopify\Models\Traits\`                          |
| `davidhirtz\yii2\shopify\modules\`                          | `Hirtz\Shopify\Modules\`                                |
| `davidhirtz\yii2\shopify\modules\admin\controllers\`        | `Hirtz\Shopify\Modules\Admin\Controllers\`              |
| `davidhirtz\yii2\shopify\modules\admin\data\`               | `Hirtz\Shopify\Modules\Admin\Data\`                     |
| `davidhirtz\yii2\shopify\modules\admin\widgets\grids\`      | `Hirtz\Shopify\Modules\Admin\Widgets\Grids\`            |
| `davidhirtz\yii2\shopify\modules\admin\widgets\navs\`       | `Hirtz\Shopify\Modules\Admin\Widgets\Navs\`             |

### Classes and files

| v2                                                                     | v3                                                                                          |
|------------------------------------------------------------------------|---------------------------------------------------------------------------------------------|
| `modules\admin\controllers\traits\ShopifyControllerTrait`              | `Components\ComponentTrait` (`static::getShopify()`)                                        |
| `modules\admin\widgets\grids\WebhookGridView`                          | `Modules\Admin\Widgets\Grids\WebhookSubscriptionGridView`                                   |
| `modules\admin\widgets\navs\ShopifySubmenu`                            | `Modules\Admin\Widgets\Navs\ShopifyNavItem` (aside), `ProductHeader` and `WebhookHeader`    |
| the footer buttons of `ProductGridView` and `WebhookGridView`          | `Modules\Admin\Widgets\Navs\ProductActionDropdown` and `WebhookActionDropdown`              |
| `migrations\M190904193339Shopify`, `M240328135307ProductVariant`, `M240624153300Json`, `M250717124737ShopifyGraphql` | `Migrations\M260101000200ShopifyBaseline` (fresh installs only) |
| `src/modules/admin/views/product/index.php`, `webhook/index.php`       | `resources/views/admin/product/index.php`, `resources/views/admin/webhook/index.php`        |
| `src/messages/`                                                        | `messages/` (`i18n.translations.shopify.basePath` is `@shopify/../messages`)                 |
| `src/components/graphql/*.graphql`                                     | `resources/graphql/*.graphql`                                                               |
| —                                                                      | `Models\Webhook`                                                                            |

### Constants

| v2                                                                     | v3                                                        |
|------------------------------------------------------------------------|-----------------------------------------------------------|
| `models\Product::AUTH_PRODUCT_UPDATE` (`shopifyProductUpdate`)         | `Models\Product::AUTH_SHOPIFY_PRODUCT` (`shopifyProduct`) |
| `models\WebhookSubscription::AUTH_WEBHOOK_UPDATE` (`shopifyWebhookUpdate`) | `Models\Webhook::AUTH_SHOPIFY_WEBHOOK` (`shopifyWebhook`) |

### Methods

| v2                                                                                   | v3                                                                                       |
|--------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------|
| `Product`, `ProductImage`, `ProductVariant::getTrailModelName()`                     | `getAdminName()` (from `Hirtz\Skeleton\Models\Traits\AdminModelTrait`)                   |
| `Product`, `ProductImage`, `ProductVariant::getTrailModelType()`                     | `getAdminType()`                                                                         |
| `ProductImage::getTrailModelAdminRoute()`                                            | removed; `getAdminRoute()` answers `false` on all three models                            |
| —                                                                                    | `Product`, `ProductImage`, `ProductVariant::getPermissionName()`                          |
| `modules\admin\Module::getDashboardPanels()`                                         | `Modules\Admin\Module::dashboard(Dashboard $dashboard)`                                  |
| `modules\admin\Module::getNavBarItems()`                                             | `Modules\Admin\Module::aside(Nav $nav)`                                                  |
| `ProductGridView::statusColumn()`, `thumbnailColumn()`, `nameColumn()`, `totalInventoryQuantityColumn()`, `variantCountColumn()`, `lastImportAtColumn()`, `buttonsColumn()` (arrays, from `init()`) | `getStatusColumn()`, `getThumbnailColumn()`, `getNameColumn()`, `getTotalInventoryQuantityColumn()`, `getVariantCountColumn()`, `getImportAtColumn()`, `getButtonColumn()` (`Column` objects, from `configure()`) |
| `ProductGridView::getCreateProductButton()`, `getUpdateAllProductsButton()`          | `ProductActionDropdown::getCreateProductButton()`, `getUpdateAllProductsButton()`        |
| `WebhookGridView::getCreateAllWebhooksButton()`                                      | `WebhookActionDropdown::getUpdateAllWebhooksButton()`                                    |
| `WebhookGridView::topicColumn()`, `apiVersionColumn()`, `updatedAtColumn()`, `buttonsColumn()` | `WebhookSubscriptionGridView::getTopicColumn()`, `getApiVersionColumn()`, `getFormatColumn()`, `getUpdatedAtColumn()`, `getButtonColumn()` |
| `Product::tableName()` through `getModule()->getTableName('product')`                | fixed `{{%product}}`, `{{%product_image}}`, `{{%product_variant}}`                        |

### Properties

| v2                                                     | v3                                                                    |
|--------------------------------------------------------|-----------------------------------------------------------------------|
| `models\Product::$contentType`                         | removed                                                               |
| `models\Product::$htmlValidator` (`array\|string`)      | `array\|string\|null`, `null` disables the validation                   |
| `modules\admin\Module::$name`                          | removed; the aside label is `ShopifyNavItem::label()` (`COMMON_PRODUCTS`) |
| `ProductGridView::$showUrl`                            | removed                                                               |
| `ShopifyControllerTrait::$shopify`                     | `static::getShopify()`                                                |
| `Module::$enableI18nTables` (skeleton `ModuleTrait`)   | removed; `Module` extends `Hirtz\Skeleton\Base\Module`                |

### Admin routes

| v2                                                          | v3                                                                  |
|-------------------------------------------------------------|---------------------------------------------------------------------|
| `admin/product/index`, `update`, `update-all`               | `admin/shopify/product/index`, `update`, `update-all`               |
| `admin/shopify-webhook/index`, `create`, `delete`           | `admin/shopify/webhook/index`, `create`, `delete`                   |
| `shopify/webhook/products-create`, `products-update`, `products-delete` | unchanged                                               |

### Columns and tables

| v2                                                                        | v3                                                                                  |
|---------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| `product.<attribute>_<language>`, `product_image.<attribute>_<language>`, `product_variant.<attribute>_<language>` | rows in the skeleton's `translation` table (`model_class`, `model_id`, `language`, `attribute`, `value`) |
| `auth_item` rows `shopifyProductUpdate`, `shopifyWebhookUpdate`           | `shopifyProduct`, `shopifyWebhook`                                                  |

### Message keys

The `shopify` category is addressed by key now. A project that overrode a message in its own `shopify.php`
renames it; a project calling `Yii::t('shopify', …)` with the English text gets the text back untranslated.

| v2                                                             | v3                                                                                      |
|----------------------------------------------------------------|-----------------------------------------------------------------------------------------|
| `Manage Shopify products`                                      | `AUTH_SHOPIFY_PRODUCT_DESCRIPTION`                                                      |
| `Manage Shopify webhooks`                                      | `AUTH_SHOPIFY_WEBHOOK_DESCRIPTION`                                                      |
| `Product`                                                      | `COMMON_PRODUCT`; as a label `PRODUCT_IMAGE_PRODUCT_ID_LABEL`, `PRODUCT_VARIANT_PRODUCT_ID_LABEL` |
| `Products`                                                     | `COMMON_PRODUCTS`                                                                       |
| `Image`                                                        | `COMMON_IMAGE`; as a label `PRODUCT_IMAGE_ID_LABEL`, `PRODUCT_VARIANT_IMAGE_ID_LABEL`   |
| `Variant`                                                      | `COMMON_VARIANT`; as a label `PRODUCT_VARIANT_ID_LABEL`                                 |
| `Variants`                                                     | `PRODUCT_VARIANT_COUNT_LABEL`                                                           |
| `Webhooks`                                                     | `COMMON_WEBHOOKS`                                                                       |
| `Shopify`, `View Products`                                     | removed; the dashboard item is `MODULE_SHOPIFY_DASHBOARD`                               |
| `View Webhooks`                                                | `WEBHOOK_ACTION_DROPDOWN_VIEW_WEBHOOKS`                                                 |
| `Install Webhooks`                                             | `WEBHOOK_ACTION_DROPDOWN_INSTALL_WEBHOOKS` (plus `WEBHOOK_ACTION_DROPDOWN_RELOAD_WEBHOOKS`) |
| `New Product`                                                  | `PRODUCT_ACTION_DROPDOWN_NEW_PRODUCT`                                                   |
| `Reload Products`                                              | `PRODUCT_ACTION_DROPDOWN_RELOAD_PRODUCTS`                                               |
| `Are you sure you want to remove this webhook?`                | `WEBHOOK_SUBSCRIPTION_REMOVE_TITLE` (the button is `WEBHOOK_SUBSCRIPTION_BUTTON_REMOVE`) |
| `All products updated via Shopify.`                            | `PRODUCT_SUCCESS_UPDATED_PRODUCTS`                                                      |
| `The product was updated via Shopify.`                         | `PRODUCT_SUCCESS_UPDATED_SHOPIFY`                                                       |
| `The product was deleted because it was not found on Shopify anymore.` | `PRODUCT_SUCCESS_DELETED`                                                       |
| `The webhook "{topic}" was created.`                           | `WEBHOOK_SUCCESS_CREATED`                                                               |
| `The webhook was deleted.`                                     | `WEBHOOK_SUCCESS_DELETED`                                                               |
| `Shopify Admin API secret key must be set to use webhooks.`    | `WEBHOOK_SHOPIFY_ADMIN_API`                                                             |
| `Title`                                                        | `PRODUCT_NAME_LABEL`, `PRODUCT_VARIANT_NAME_LABEL`                                      |
| `Description`                                                  | `PRODUCT_CONTENT_LABEL`                                                                 |
| `Shopify slug`                                                 | `PRODUCT_SLUG_LABEL`                                                                    |
| `Vendor`                                                       | `PRODUCT_VENDOR_LABEL`                                                                  |
| `Type`                                                         | `PRODUCT_PRODUCT_TYPE_LABEL`                                                            |
| `Inventory`                                                    | `PRODUCT_TOTAL_INVENTORY_QUANTITY_LABEL`                                                |
| `Last import`                                                  | `PRODUCT_LAST_IMPORT_AT_LABEL`                                                          |
| `Alt text`                                                     | `PRODUCT_IMAGE_ALT_TEXT_LABEL`                                                          |
| `Height`                                                       | `PRODUCT_IMAGE_HEIGHT_LABEL`                                                            |
| `Position`                                                     | `PRODUCT_IMAGE_POSITION_LABEL`, `PRODUCT_VARIANT_POSITION_LABEL`                        |
| `Weight`                                                       | `PRODUCT_IMAGE_WEIGHT_LABEL`, `PRODUCT_VARIANT_WEIGHT_LABEL`                            |
| `URL`                                                          | `PRODUCT_IMAGE_SRC_LABEL`, `WEBHOOK_ADDRESS_LABEL`, `WEBHOOK_SUBSCRIPTION_ADDRESS_LABEL` |
| `Price`                                                        | `PRODUCT_VARIANT_PRICE_LABEL`                                                           |
| `Compare at price`                                             | `PRODUCT_VARIANT_COMPARE_AT_PRICE_LABEL`                                                |
| `Option 1`, `Option 2`, `Option 3`                             | `PRODUCT_VARIANT_OPTION_1_LABEL`, `PRODUCT_VARIANT_OPTION_2_LABEL`, `PRODUCT_VARIANT_OPTION_3_LABEL` |
| `Barcode (ISBN, UPC, GTIN, etc.)`                              | `PRODUCT_VARIANT_BARCODE_LABEL`                                                         |
| `SKU (Stock Keeping Unit)`                                     | `PRODUCT_VARIANT_SKU_LABEL`                                                             |
| `Taxable`                                                      | `PRODUCT_VARIANT_IS_TAXABLE_LABEL`                                                      |
| `Weight unit`                                                  | `PRODUCT_VARIANT_WEIGHT_UNIT_LABEL`                                                     |
| `Unit price`                                                   | `PRODUCT_VARIANT_UNIT_PRICE_LABEL`                                                      |
| `Unit price measurement`                                       | `PRODUCT_VARIANT_UNIT_PRICE_MEASUREMENT_LABEL`                                          |
| `Inventory tracking`                                           | `PRODUCT_VARIANT_INVENTORY_TRACKED_LABEL`                                               |
| `Quantity`                                                     | `PRODUCT_VARIANT_INVENTORY_QUANTITY_LABEL`                                              |
| `Inventory policy`                                             | `PRODUCT_VARIANT_INVENTORY_POLICY_LABEL`                                                |
| `Event`                                                        | `WEBHOOK_TOPIC_LABEL`, `WEBHOOK_SUBSCRIPTION_TOPIC_LABEL`                               |
| `Format`                                                       | `WEBHOOK_FORMAT_LABEL`, `WEBHOOK_SUBSCRIPTION_FORMAT_LABEL`                             |
| `API Version`                                                  | `WEBHOOK_API_VERSION_LABEL`, `WEBHOOK_SUBSCRIPTION_API_VERSION_LABEL`                   |

## Configuration

**The credentials stay in `config/params.php`** under the same names (`shopifyShopName`, `shopifyShopDomain`,
`shopifyApiKey`, `shopifyApiSecret`, `shopifyAccessToken`, `shopifyStorefrontAccessToken`), read by the
`shopify` component. Nothing on `modules.shopify` is read except `webhooks`; a credential placed there is
ignored.

**A project that declares `components.shopify` itself must name the class.** The application validates the
component definitions before the bundle's `Bootstrap` can supply it, and `ComponentTrait::getShopify()` throws
for anything that is not a `ShopifyComponent`:

```php
// before
'components' => [
    'shopify' => [
        'shopifyApiVersion' => '2025-01',
    ],
],

// after
'components' => [
    'shopify' => [
        'class' => \Hirtz\Shopify\Components\ShopifyComponent::class,
        'shopifyApiVersion' => '2025-01',
    ],
],
```

**`modules.shopify.webhooks`** keeps its shape, a list of `['topic' => …, 'route' => […]]`.

**Translated attributes** are still declared through the container, and the shape is unchanged:

```php
'container' => [
    'definitions' => [
        \Hirtz\Shopify\Models\Product::class => [
            'i18nAttributes' => ['name', 'content', 'slug'],
        ],
    ],
],
```

Only the storage moved (see [Data and schema](#data-and-schema)). `Module::$enableI18nTables` is gone with the
skeleton `ModuleTrait` the module used to carry; the tables were never per language.

## Code changes

### Namespace

Replace `davidhirtz\yii2\shopify\` with `Hirtz\Shopify\` and StudlyCase the path segments after it
(`models\queries\ProductQuery` is `Models\Queries\ProductQuery`), in `use` statements, container definitions
and string class names alike.

### Admin routes

The two admin controllers live in the `admin/shopify` submodule. Every link and redirect a project builds
changes its prefix:

```php
// before
['/admin/product/index']
['/admin/shopify-webhook/create']

// after
['/admin/shopify/product/index']
['/admin/shopify/webhook/create']
```

The public webhook endpoints (`shopify/webhook/products-create` and friends) are unchanged, so the
subscriptions registered in Shopify keep working.

### Permissions

`Product::AUTH_PRODUCT_UPDATE` is `Product::AUTH_SHOPIFY_PRODUCT`, `WebhookSubscription::AUTH_WEBHOOK_UPDATE`
is `Webhook::AUTH_SHOPIFY_WEBHOOK`. An `AccessControl` rule, a `can()` check or a nav item naming either
constant is renamed; the stored assignments are carried over by the data migration.

### The `shopify` component

`ShopifyControllerTrait` and its `$shopify` property are gone. Anything that read `Yii::$app->get('shopify')`
uses `Components\ComponentTrait`:

```php
// before
$api = Yii::$app->get('shopify')->getAdminApi();

// after
use Hirtz\Shopify\Components\ComponentTrait;

$api = static::getShopify()->getAdminApi();
```

### Admin model methods

`Product`, `ProductImage` and `ProductVariant` implement the skeleton's `TrailModelInterface`, which extends
`AdminModelInterface`. A project subclass overriding the trail naming renames its overrides:

```php
// before
public function getTrailModelType(): string { return Yii::t('app', 'Merch'); }

// after
public function getAdminType(): string { return Yii::t('app', 'Merch'); }
```

`getTrailModelName()` is `getAdminName()` and is answered by `AdminModelTrait`; `getTrailModelAdminRoute()` is
gone and `getAdminRoute()` returns `false` on all three (a product is edited in Shopify, not in the admin).
A subclass implementing `AdminModelInterface` itself must also answer `getPermissionName()`.

### Translations

The API on the record is unchanged: `getI18nAttribute('name')`, `getI18nAttributeName('name')` and the
`name_de` magic attribute all still work. What changes is the query side, because the translated values are
no longer columns:

- `Product::find()` returns `Models\Queries\ProductQuery`, now an `I18nActiveQuery`; `ProductImage::find()`
  and `ProductVariant::find()` return a plain `Hirtz\Skeleton\Db\I18nActiveQuery`. A project query class
  extending `ProductQuery` inherits that.
- A `where(['name_de' => …])` or an `orderBy('name_de')` on raw SQL no longer resolves. `orderBy()` on the
  query rewrites a translated attribute itself; a condition on a translation joins the `translation` table.
- A query for more than one record eager loads every configured language; `withoutTranslations()` opts out.

### `Product::$contentType`

The property was always `html` and only gated the model's own rule. A project that turned the HTML validation
off sets the validator to `null` instead:

```php
// before
Product::class => ['contentType' => false],

// after
Product::class => ['htmlValidator' => null],
```

### Admin widgets and views

The admin is built on the skeleton's 3.0 widget layer. `ProductGridView` extends
`Hirtz\Skeleton\Widgets\Grids\GridView`: its columns are `Column` objects assembled in `configure()`, its
header holds the status dropdown and the search input, and its footer buttons moved into
`ProductActionDropdown`, rendered by `ProductHeader`. `WebhookGridView` is `WebhookSubscriptionGridView`, with
`WebhookActionDropdown` and `WebhookHeader` beside it. `ShopifySubmenu` is gone: the aside item is
`ShopifyNavItem` (with *Webhooks* as its subnav item), and the pages carry a header instead of a submenu.

A project view rendering the product index writes:

```php
echo ProductHeader::make()
    ->provider($provider);

echo GridContainer::make()
    ->grid(ProductGridView::make()
        ->provider($provider));
```

A project that renamed the module through `modules.admin.modules.shopify.name` relabels the nav item from a
`Widget::EVENT_CONFIGURE` listener on `ShopifyNavItem` calling `label()`, and the dashboard item through
`Modules\Admin\Module::dashboard()`.

### Views and messages

A project overriding the bundle's views points its `viewPath` at `resources/views/admin/product` and
`resources/views/admin/webhook`, and its own `i18n.translations.shopify` entry at `@shopify/../messages`.

## Data and schema

The bundle ships one migration, `Migrations\M260101000200ShopifyBaseline`, which creates the three tables and
the two permissions for a **fresh** install. It must not run against an upgraded database. The v2 to v3
migrations live in `davidhirtz/yii2-upgrade` under `migrations/yii2-shopify/`, and the upgrade tool collapses
the four v2 rows of the `migration` table (`davidhirtz\yii2\shopify\migrations\M…`) into one row naming the
baseline before it applies them.

Before: a database dump. The skeleton's `M260910100000Translation` runs first by timestamp and creates the
`translation` table the first migration below writes into; its `model` column is renamed to `model_class` by a
later skeleton migration, and the trait writes whichever name the table carries at that moment.

1. `M260910150000Translations` moves every `<attribute>_<language>` column of `product`, `product_image` and
   `product_variant` into the `translation` table, one row per non-empty value keyed by the model class, then
   drops the column and every index containing it (`slug_<language>`, `name_<language>`). It reads the
   columns off the table, so an attribute a project has since removed from `i18nAttributes` is migrated rather
   than stranded. Its `safeDown()` recreates the columns and the two product indexes from the rows.
2. `M260914160000AuthItems` adds `shopifyProduct` and `shopifyWebhook` under `admin`, gives each new item
   every parent role and every user assignment the old `shopifyProductUpdate` / `shopifyWebhookUpdate` had,
   and deletes the old items. Trap: a fresh install grants both to `manager` as well; an upgraded database
   does so only if `manager` held the old item. Grant it by hand if that is what the project expects.

After: nothing to rebuild. Products are not searchable and have no permalinks, and the webhook callback URLs
are unchanged. `./yii shopify/import` is optional and only refreshes the product data from Shopify.

Lost: an empty translation (`''`) is not carried into the `translation` table; it reads back as `null`, which
`getI18nAttribute()` treats the same way.

## Removed

- `Product::$contentType`. The description is HTML; `Product::$htmlValidator = null` turns the validation off.
- `ProductGridView::$showUrl` and the frontend URL it rendered under the product name.
- `Modules\Admin\Module::$name`, `getDashboardPanels()` and `getNavBarItems()`; see [Admin widgets and
  views](#admin-widgets-and-views).
- `ShopifyControllerTrait`; see [The `shopify` component](#the-shopify-component).
- The skeleton `ModuleTrait` on `Module` (`enableI18nTables`, `getTableName()`).
- Not carried over from 2.3.0: the automatic generation of `shopifyAccessToken` through the GraphQL API
  (`components\ShopifyAccessToken`) and the `shopify/storefront-access-token` command
  (`components\admin\StorefrontAccessTokenCreate`). `ShopifyComponent::getAdminApi()` requires
  `shopifyShopName` and `shopifyAccessToken`, so a shop set up on 2.3.x without a stored access token creates
  one in the Shopify admin and adds it to `params` before upgrading. The default API version is `2025-07`;
  set `components.shopify.shopifyApiVersion` to keep `2026-04`.
