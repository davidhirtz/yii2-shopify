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

- Updated admin according to `davidhirtz\yii2\skeleton\modules\admin\ModuleInterface`

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

- Replaced `ActiveRecord::logErrors()` with `\davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger::log()

## 2.0.1 (Nov 6, 2023)

- Moved `Bootstrap` class to base package namespace for consistency

## 2.0.0 (Nov 3, 2023)

- Moved source code to `src` folder
- Moved all models, data providers and widgets out of `base` folder, to override them use Yii's dependency injection
  container
- Removed `FrontendAssetBundle`, use NPM package `shopify-buy-cart` instead.
- Changed namespaces from `davidhirtz\yii2\shopify\admin\widgets\grid`
  to `davidhirtz\yii2\shopify\admin\widgets\grids` and `davidhirtz\yii2\skeleton\shopify\widgets\nav`
  to `davidhirtz\yii2\shopify\admin\widgets\navs`

## 1.1.7 (Oct 31, 2023)

- Locked `davidhirtz\yii2-skeleton` to version `^1.9`, upgrade to version 2.0 to use the latest version of this package