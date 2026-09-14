<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Override;
use Stringable;
use Yii;

class ProductHeader extends Header
{
    /**
     * @use ProviderTrait<ProductActiveDataProvider>
     */
    use ProviderTrait;

    #[Override]
    protected function configure(): void
    {
        $this->title ??= Yii::t('shopify', 'COMMON_PRODUCTS');
        $this->subtitle ??= $this->getPaginationSubtitle($this->provider);

        $this->addContent($this->getProductActionDropdown());

        parent::configure();
    }

    protected function getProductActionDropdown(): ?Stringable
    {
        return ProductActionDropdown::make();
    }
}
