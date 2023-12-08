## v2.0.2 (Dec 8, 2023)

- Replaced `ActiveRecord::logErrors()` with `\davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger::log()

## v2.0.1 (Nov 6, 2023)

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