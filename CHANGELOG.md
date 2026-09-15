## 3.0.0 (in development)


- **`Components\ComponentTrait::getShopify()` replaces `Yii::$app->get('shopify')`** at all nineteen call sites
  and throws when the id holds something else — which a project's own `components.shopify` entry produces
  whenever it omits `class`, since `Application` validates the definitions before `Bootstrap` can supply one

- `Models\Product::$contentType` is gone — it was always `html` and read by nothing but the model's own rule.
  `$htmlValidator` is nullable now and disables the validation when set to `null`

- `Components\ShopifyPrice` rounds to the cent instead of truncating the product of a float: `(int)(19.99 * 100)`
  is 1998, so a variant priced `19.99`, `0.29` or `1.15` was stored one cent short
- `Components\ShopifyId` reads the last path segment without `strrchr()`, which returns `false` — and so is a
  `TypeError` under `substr()` — for the bare numeric id a webhook payload carries
- `Module` extends `Skeleton\Base\Module`, so its `controllerNamespace` is `Hirtz\Shopify\Controllers` rather
  than Yii's lowercase default, which PSR-4 never resolves on a case-sensitive filesystem. The public
  `shopify/webhook/*` endpoints only ever answered on a case-insensitive one
- `Models\Webhook` reads the API version off the `shopify` component rather than the module, which defaulted to a
  different one than `Components\Admin\AdminApi` calls the API with. The module's own credential properties are
  read by nothing — **configure `params`, not `modules.shopify`**
- `Bootstrap` maps the console controller for a console *application* rather than for the CLI SAPI, which a web
  application under PHPUnit also reports, hiding the `shopify` module's own routes behind it
- `Modules\Admin\Controllers\WebhookController::actionCreate()` reports only the errors each topic's own call
  added. `WebhookSubscriptionMutation` accumulates them, so one address already taken silenced the success of
  every topic after it
- `Modules\Admin\Controllers\ProductController::actionUpdate()` no longer calls `delete()` on `null` for a
  product that is in neither Shopify nor the database
- `Components\GraphqlParser` marks a fragment before following it, so two fragments spreading each other
  terminate, and reports a document that is not there instead of passing `false` to `preg_match_all()`
- `Models\Webhook` uses `Base\Traits\ModelTrait`, as `WebhookSubscription` already did
- **One permission per admin-managed model.** `Models\Product::AUTH_SHOPIFY_PRODUCT` (`shopifyProduct`) replaces
  `AUTH_PRODUCT_UPDATE` and `Models\Webhook::AUTH_SHOPIFY_WEBHOOK` (`shopifyWebhook`) replaces
  `AUTH_WEBHOOK_UPDATE`, so the constant and its value agree on the prefix again;
  `Models\WebhookSubscription`'s duplicate of the webhook constant is gone. Their descriptions are
  `AUTH_SHOPIFY_PRODUCT_DESCRIPTION` and `AUTH_SHOPIFY_WEBHOOK_DESCRIPTION`.
  `Migrations\M260914160000AuthItems` grants each new item to every parent and assignee of the old one
- `Models\Product`, `Models\ProductImage` and `Models\ProductVariant` implement the skeleton's
  `Models\Interfaces\AdminModelInterface` in place of `AdminRouteInterface`: `getTrailModelName()` and
  `getTrailModelType()` are `getAdminName()` and `getAdminType()`, and the boilerplate name is
  `Models\Traits\AdminModelTrait`'s
- `Models\Product`, `Models\ProductImage` and `Models\ProductVariant` implement the skeleton
  `Models\Interfaces\AdminRouteInterface` and dropped their `getTrailModelAdminRoute()`
- Translated attributes of `Product`, `ProductImage` and `ProductVariant` moved from their `_xx` columns into
  the skeleton's `translation` table (`M260910150000Translations`); `ProductQuery` extends `I18nActiveQuery`
- Changed the webhook URL rule to a `Route` registered via `Application::addRoutes()`
- Fixed `ProductVariant::$inventory_tracked` missing on a fresh install: `M250717124737ShopifyGraphql` only added
  the column when the v2 `inventory_management` column was present

## 2.2.1 (Nov 13, 2025)

- Fixed `shopifyShopDomain` containing HTTP protocol
- Added `Product::$slug` unique validation rule

## 2.2.0 (Jul 28, 2025)

- Added `Product::$unit_price` and `Product::$unit_price_measurement`
- Added `ShopifyComponent::$defaultCurrency` property
- Changed the primary key for `ProductImage` to include the `product_id` (Issue #12)
- Changed `Product::$price` and `Product::$compare_at_price` to integer (Issue #11)
- Changed `Product::$tags` from string to array
- Changed `Product::$total_inventory_quantity` and `ProductVariant::$inventory_quantity` to allow negative values
- Improved `ProductImage::beforeDelete()`
- Removed `Product::$grams`
- Replaced Shopify Admin REST API with GraphQL API
- Replaced `ProductVariant::$inventory_management` with `ProductVariant::$inventory_tracked` (Issue #9)

## 2.1.12 (Mar 31, 2025)

- Added `ProductActiveDataProvider` default sort
- Added `Webhook::$metafield_identifiers`
- Fixed empty `Module::$shopifyShopDomain` default

## 2.1.11 (Mar 24, 2025)

- Changed Shopify credentials defaults from null-coalescing to ternary operator
- Enhanced Shop URL for single variant products
- Fixed empty `Product::$variant_id` on first import

## 2.1.10 (Jan 28, 2025)

- Changed `Bootstrap` I18N configuration

## 2.1.9 (Nov 29, 2024)

- Fixed `ProductGridView` button method signature
- Forced strict types for all PHP files

## 2.1.8 (Aug 19, 2024)

- Changed `Bootstrap` to use `ApplicationTrait::addUrlManagerRules()` to prevent the initialization of the URL manager
  before the bootstrap is completed
-

## 2.1.7 (Jun 24, 2024)

- Fixed MySQL JSON columns bug via migration. This normalizes JSON columns for MariaDB and MySQL with the introduction
  of JSON support in Yii 2.0.49.

## 2.1.6 (Jun 10, 2024)

- Added `unique` validation rule for all model IDs updated via webhook

## 2.1.5 (May 7, 2024)

- Fixed `inventory_quantity` for `-1` API values

## 2.1.4 (Apr 5, 2024)

- Updated admin according to `Hirtz\Skeleton\Modules\Admin\ModuleInterface`

## 2.1.3 (Apr 3, 2024)

- Fixed default `Product::$variant_id` if variants to start at position 1

## 2.1.2 (Mar 28, 2024)

- Fixed `inventory_quantity` allowing `null` values (Issue #3)
- Fixed API error handling (Issue #2)

## 2.1.1 (Jan 29, 2024)

- Minor improvements
- Updated dependencies

## 2.1.0 (Dec 20, 2023)

- Added Codeception test suite
- Added GitHub Actions CI workflow

## 2.0.2 (Dec 8, 2023)

- Replaced `ActiveRecord::logErrors()` with `\Hirtz\Skeleton\Log\ActiveRecordErrorLogger::log()

## 2.0.1 (Nov 6, 2023)

- Moved `Bootstrap` class to base package namespace for consistency

## 2.0.0 (Nov 3, 2023)

- Moved source code to `src` folder
- Moved all models, data providers and widgets out of `base` folder, to override them use Yii's dependency injection
  container
- Removed `FrontendAssetBundle`, use NPM package `shopify-buy-cart` instead.
- Changed namespaces from `Hirtz\Shopify\admin\widgets\grid`
  to `Hirtz\Shopify\admin\widgets\grids` and `Hirtz\Skeleton\Shopify\widgets\nav`
  to `Hirtz\Shopify\admin\widgets\navs`

## 1.1.7 (Oct 31, 2023)

- Locked `davidhirtz\yii2-skeleton` to version `^1.9`, upgrade to version 2.0 to use the latest version of this package