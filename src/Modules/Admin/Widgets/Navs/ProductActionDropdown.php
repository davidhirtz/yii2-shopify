<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Navs;

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
            ->href(Yii::$app->get('shopify')->getShopUrl('admin/products/new'))
            ->icon('external-link')
            ->text(Yii::t('shopify', 'New Product'))
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
            ->text(Yii::t('shopify', 'Reload Products'))
            ->post(['/admin/shopify/product/update-all']);
    }
}
