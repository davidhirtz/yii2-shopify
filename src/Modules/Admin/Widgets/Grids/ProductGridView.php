<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Widgets\Grids;

use Hirtz\Shopify\Models\Product;
use Hirtz\Shopify\Modules\Admin\Controllers\ProductController;
use Hirtz\Shopify\Modules\Admin\Data\ProductActiveDataProvider;
use Hirtz\Shopify\Modules\ModuleTrait;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Html\Img;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Grids\Columns\BadgeColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\PropertyColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\RelativeTimeColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\GridSearchForm;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\GridToolbarItem;
use Hirtz\Skeleton\Widgets\Grids\Traits\StatusGridViewTrait;
use Iterator;
use Override;
use Stringable;
use Yii;

/**
 * @property ProductActiveDataProvider $provider
 */
class ProductGridView extends GridView
{
    use ModuleTrait;
    use StatusGridViewTrait;

    #[Override]
    protected function configure(): void
    {
        $this->header ??= [
            $this->getStatusDropdown(),
            GridSearchForm::make()->grid($this),
        ];

        $this->columns ??= [
            $this->getStatusColumn(),
            $this->getThumbnailColumn(),
            $this->getNameColumn(),
            $this->getTotalInventoryQuantityColumn(),
            $this->getVariantCountColumn(),
            $this->getUpdatedAtColumn(),
            $this->getButtonColumn(),
        ];

        $this->footer ??= [
            $this->getCreateProductButton(),
            GridToolbarItem::make()
                ->class('ms-auto')
                ->content($this->getUpdateAllProductsButton()),
        ];

        parent::configure();
    }

    protected function getThumbnailColumn(): ?Column
    {
        return Column::make()
            ->headerAttributes(['class' => 'grid-col-thumbnail'])
            ->content($this->getThumbnailColumnContent(...));
    }

    protected function getThumbnailColumnContent(Product $product): ?Stringable
    {
        return $product->image
            ? Img::make()
                ->src($product->image->getUrl(['width' => 320, 'height' => 320]))
                ->class('img-thumbnail')
                ->loading('lazy')
            : null;
    }

    protected function getNameColumn(): ?Column
    {
        return PropertyColumn::make()
            ->property(Product::instance()->getI18nAttributeName('name'))
            ->content($this->getNameColumnContent(...));
    }

    protected function getNameColumnContent(Product $product): ?Stringable
    {
        $name = $product->getI18nAttribute('name') ?? '';

        return A::make()
            ->class('strong')
            ->content($this->search->markKeywords($name))
            ->href($product->getShopifyAdminUrl())
            ->target('_blank');
    }

    protected function getTotalInventoryQuantityColumn(): ?Column
    {
        return BadgeColumn::make()
            ->property('inventory_quantity')
            ->url(fn (Product $product) => $product->getShopifyAdminUrl());
    }

    protected function getVariantCountColumn(): ?Column
    {
        return BadgeColumn::make()
            ->property('variant_count')
            ->blank()
            ->url($this->getProductShopUrl(...));
    }

    protected function getProductShopUrl(Product $product): string
    {
        return $product->getShopifyAdminUrl();
    }

    protected function getUpdatedAtColumn(): ?Column
    {
        return RelativeTimeColumn::make()
            ->property('updated_at')
            ->hiddenForMediumDevices();
    }

    protected function getButtonColumn(): ?Column
    {
        return ButtonColumn::make()
            ->content($this->getButtonColumnContent(...));
    }

    protected function getButtonColumnContent(Product $product): Iterator
    {
        yield $this->getShopifyAdminProductButton($product);
        yield $this->getUpdateButton($product);
    }

    protected function getCreateProductButton(): string|Stringable
    {
        return Button::make()
            ->primary()
            ->text(Yii::t('shopify', 'New Product'))
            ->href(Yii::$app->get('shopify')->getShopUrl('admin/products/new'))
            ->target('_blank');
    }

    /**
     * @see ProductController::actionUpdateAll()
     */
    protected function getUpdateAllProductsButton(): ?Stringable
    {
        return Button::make()
            ->primary()
            ->text(Yii::t('shopify', 'Reload Products'))
            ->icon('sync')
            ->post(['/admin/shopify/product/update-all']);
    }

    protected function getUpdateButton(Product $product): ?Stringable
    {
        return Button::make()
            ->primary()
            ->icon('sync')
            ->post(['/admin/shopify/product/update', 'id' => $product->id]);
    }

    protected function getShopifyAdminProductButton(Product $product): ?Stringable
    {
        return Button::make()
            ->secondary()
            ->icon('wrench')
            ->href($product->getShopifyAdminUrl())
            ->target('_blank');
    }
}
