<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Skeleton\Models\Breadcrumb;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Stringable;
use Yii;

class ProductHeader extends Header
{
    /**
     * @use ProviderTrait<ProductActiveDataProvider>
     */
    use ProviderTrait;

    #[\Override]
    protected function configure(): void
    {
        $this->breadcrumbs ??= [
            new Breadcrumb(Lang::t('shopify', 'COMMON_SHOPIFY'), ['/admin/shopify/product/index']),
        ];

        $this->title ??= Lang::t('shopify', 'COMMON_PRODUCTS');
        $this->subtitle ??= $this->getPaginationSubtitle($this->provider);

        $this->addContent($this->getProductActionDropdown());

        parent::configure();
    }

    protected function getProductActionDropdown(): ?Stringable
    {
        return ProductActionDropdown::make();
    }
}
