## 3.0.0 (in development)

- Renamed the namespace from `davidhirtz\yii2\shopify\` to `Hirtz\Shopify\` and every directory to StudlyCase (`models\queries` is `Models\Queries`); the views moved to `resources/views/admin/`, the messages to `messages/` and the GraphQL documents to `resources/graphql/`
- Moved the admin controllers into the `admin/shopify` submodule: `admin/product/*` is `admin/shopify/product/*` and `admin/shopify-webhook/*` is `admin/shopify/webhook/*`
- Renamed the permissions `shopifyProductUpdate` to `shopifyProduct` (`Models\Product::AUTH_SHOPIFY_PRODUCT`) and `shopifyWebhookUpdate` to `shopifyWebhook` (`Models\Webhook::AUTH_SHOPIFY_WEBHOOK`); removed `Models\WebhookSubscription::AUTH_WEBHOOK_UPDATE`
- Replaced `getTrailModelName()`, `getTrailModelType()` and `getTrailModelAdminRoute()` on `Product`, `ProductImage` and `ProductVariant` with the skeleton's `AdminModelInterface` (`getAdminName()`, `getAdminType()`, `getAdminRoute()`, `getPermissionName()`)
- Moved the translated attributes of `Product`, `ProductImage` and `ProductVariant` from their `_xx` columns into the skeleton's `translation` table; all three implement `TranslationInterface`, `Models\Queries\ProductQuery` extends `I18nActiveQuery` and `ProductImage::find()` / `ProductVariant::find()` return one
- Removed `Product::$contentType`; `Product::$htmlValidator` is nullable and `null` disables the validation
- Replaced `ShopifyControllerTrait` and `Yii::$app->get('shopify')` with `Components\ComponentTrait::getShopify()`, which throws unless the component is a `ShopifyComponent`, so a project's own `components.shopify` entry must name its `class`
- Replaced `ShopifySubmenu`, `WebhookGridView` and the admin module's `$name`, `getDashboardPanels()` and `getNavBarItems()` with `Modules\Admin\Widgets\Navs\ShopifyNavItem`, `ProductHeader`, `WebhookHeader`, `ProductActionDropdown`, `WebhookActionDropdown` and `Grids\WebhookSubscriptionGridView`; `ProductGridView` extends the skeleton's `Widgets\Grids\GridView`
- Changed every message key to `UPPER_SNAKE_CASE` (`PRODUCT_NAME_LABEL`, `WEBHOOK_SUCCESS_CREATED`, `AUTH_SHOPIFY_PRODUCT_DESCRIPTION`)
- Changed `Models\Webhook` to read the API version from the `shopify` component; the credential properties on `Module` are read by nothing, configure `components.shopify` or `params` instead
- Changed `Module` to extend `Hirtz\Skeleton\Base\Module`, so the `shopify/webhook/*` endpoints resolve on a case-sensitive filesystem
- Changed `Product::getShopifyAdminUrl()` to link the variant of a product that has more than one
- Replaced the historical migrations with `Migrations\M260101000200ShopifyBaseline`; the v2 to v3 migrations live in `davidhirtz/yii2-upgrade`
- Fixed `Components\ShopifyPrice` storing a price one cent short, `Components\ShopifyId` failing on a bare numeric id and `Components\GraphqlParser` looping on two fragments spreading each other
- Added `Models\Webhook` with `getTopics()` and `getFormattedTopic()`

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