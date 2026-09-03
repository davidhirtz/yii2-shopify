<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Navs\ActionDropdown;
use Stringable;
use Yii;

class ProductActionDropdown extends ActionDropdown
{
    public function __construct(array $config = [])
    {
        $this->addDefaultItems();
        parent::__construct($config);
    }

    protected function addDefaultItems(): void
    {
        $this->addItem($this->getCreateProductButton(), $this->getUpdateAllProductsButton());
    }

    protected function getCreateProductButton(): string|Stringable
    {
        return Button::make()
            ->primary()
            ->url(Yii::$app->get('shopify')->getShopUrl('admin/products/new'))
            ->icon('external-link')
            ->text(Lang::t('shopify', 'PRODUCT_ACTION_DROPDOWN_NEW_PRODUCT'))
            ->target('_blank');
    }

    /**
     * @see ProductController::actionUpdateAll()
     */
    protected function getUpdateAllProductsButton(): ?Stringable
    {
        return Button::make()
            ->primary()
            ->icon('sync')
            ->text(Lang::t('shopify', 'PRODUCT_ACTION_DROPDOWN_RELOAD_PRODUCTS'))
            ->post(['/admin/shopify/product/update-all']);
    }
}
