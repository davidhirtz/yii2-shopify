<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

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

    protected function configure(): void
    {
        $this->breadcrumbs ??= [
            new Breadcrumb(Yii::t('shopify', 'Shopify'), ['/admin/shopify/product/index']),
        ];

        $this->title ??= Yii::t('shopify', 'Products');
        $this->subtitle ??= $this->getPaginationSubtitle($this->provider);

        $this->addContent($this->getProductActionDropdown());

        parent::configure();
    }

    protected function getProductActionDropdown(): ?Stringable
    {
        return ProductActionDropdown::make();
    }
}
