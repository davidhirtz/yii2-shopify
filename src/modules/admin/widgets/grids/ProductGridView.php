<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\grids;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\admin\controllers\ProductController;
use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\columns\CounterColumn;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\traits\StatusGridViewTrait;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\TimeagoColumn;
use Yii;

/**
 * @property ProductActiveDataProvider $dataProvider
 */
class ProductGridView extends GridView
{
    use ModuleTrait;
    use StatusGridViewTrait;

    /**
     * @var bool whether product urls should be displayed in the name column
     */
    public bool $showUrl = false;

    public function init(): void
    {
        if (!$this->columns) {
            $this->columns = [
                $this->statusColumn(),
                $this->thumbnailColumn(),
                $this->nameColumn(),
                $this->totalInventoryQuantityColumn(),
                $this->variantCountColumn(),
                $this->updatedAtColumn(),
                $this->buttonsColumn(),
            ];
        }

        $this->status = $this->dataProvider->status;

        parent::init();
    }

    protected function initHeader(): void
    {
        $this->header ??= [
            [
                [
                    'content' => $this->statusDropdown(),
                    'options' => ['class' => 'col-12 col-md-3'],
                ],
                [
                    'content' => $this->getSearchInput(),
                    'options' => ['class' => 'col-12 col-md-6'],
                ],
                'options' => [
                    'class' => 'justify-content-between',
                ],
            ],
        ];
    }

    protected function initFooter(): void
    {
        $this->footer ??= [
            [
                [
                    'content' => $this->getCreateProductButton(),
                    'options' => ['class' => 'col'],
                ],
                [
                    'content' => $this->getUpdateAllProductsButton(),
                    'options' => ['class' => 'col text-right'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function thumbnailColumn(): array
    {
        return [
            'headerOptions' => ['style' => 'width:150px'],
            'content' => function (Product $product) {
                if (!$product->image_id) {
                    return '';
                }

                $html = Html::tag('div', '', [
                    'style' => 'background-image:url(' . $product->image->getUrl(['width' => 300, 'height' => 300]) . ');',
                    'class' => 'thumb',
                ]);

                return Html::a($html, $product->getAdminRoute(), [
                    'target' => '_blank',
                ]);
            }
        ];
    }

    public function nameColumn(): array
    {
        return [
            'attribute' => $this->getModel()->getI18nAttributeName('name'),
            'content' => function (Product $product) {
                $html = Html::markKeywords(Html::encode($product->getI18nAttribute('name') ?? ''), $this->search);
                $html = Html::tag('strong', Html::a($html, $product->getAdminRoute(), [
                    'target' => '_blank',
                ]));

                if ($this->showUrl) {
                    $html .= $this->getUrl($product);
                }

                return $html;
            }
        ];
    }

    public function totalInventoryQuantityColumn(): array
    {
        return [
            'attribute' => 'total_inventory_quantity',
            'class' => CounterColumn::class,
            'route' => fn(Product $product) => $product->getAdminRoute(),
        ];
    }

    public function variantCountColumn(): array
    {
        return [
            'attribute' => 'variant_count',
            'class' => CounterColumn::class,
            'route' => fn(Product $product) => static::getModule()->getShopUrl("admin/products/$product->id/variants/$product->variant_id"),
            'countHtmlOptions' => [
                'class' => 'badge',
                'target' => '_blank',
            ]
        ];
    }

    public function updatedAtColumn(): array
    {
        return [
            'attribute' => 'updated_at',
            'class' => TimeagoColumn::class,
        ];
    }

    public function buttonsColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (Product $product) {
                return Html::buttons($this->getRowButtons($product));
            }
        ];
    }

    protected function getRowButtons(Product $product): array
    {
        return [
            $this->getUpdateButton($product),
            $this->getShopifyAdminProductButton($product),
        ];
    }

    protected function getUrl(Product $product): string
    {
        if ($route = $product->getRoute()) {
            $urlManager = Yii::$app->getUrlManager();
            $url = $product->isEnabled() ? $urlManager->createAbsoluteUrl($route) : $urlManager->createDraftUrl($route);

            if ($url) {
                return Html::tag('div', Html::a($url, $url, ['target' => '_blank']), ['class' => 'd-none d-md-block small']);
            }
        }

        return '';
    }

    protected function getCreateProductButton(): string
    {
        return Html::a(Html::iconText('plus', Yii::t('shopify', 'New Product')), static::getModule()->getShopUrl('admin/products/new'), [
            'class' => 'btn btn-primary',
            'target' => '_blank',
        ]);
    }

    /**
     * @see ProductController::actionUpdateAll()
     */
    protected function getUpdateAllProductsButton(): string
    {
        return Html::a(Html::iconText('sync', Yii::t('shopify', 'Reload Products')), ['/admin/product/update-all'], [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    protected function getUpdateButton($model): string
    {
        return Html::a(Icon::tag('sync'), $this->getRoute($model), [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    protected function getShopifyAdminProductButton(Product $product): string
    {
        return Html::a(Icon::tag('wrench'), $product->getAdminRoute(), [
            'class' => 'btn btn-primary d-none d-md-inline-block',
            'target' => '_blank'
        ]);
    }

    public function getModel(): Product
    {
        return Product::instance();
    }
}