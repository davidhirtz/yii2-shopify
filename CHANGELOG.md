# Version v2.0.0

- Moved source code to `src` folder
- Moved all models, data providers and widgets out of `base` folder, to override them use Yii's dependency injection
  container
- Removed `FrontendAssetBundle`, use NPM package `shopify-buy-cart` instead.
- Changed namespaces from `davidhirtz\yii2\shopify\admin\widgets\grid`
  to `davidhirtz\yii2\shopify\admin\widgets\grids` and `davidhirtz\yii2\skeleton\shopify\widgets\nav`
  to `davidhirtz\yii2\shopify\admin\widgets\navs`

# Version v1.1.7

- Locked `davidhirtz\yii2-skeletin` to version `^1.9`, upgrade to version 2.0 to use the latest version of this package