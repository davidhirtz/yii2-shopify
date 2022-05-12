<?php

namespace davidhirtz\yii2\shopify\modules\admin\widgets\grid\base;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\modules\admin\data\ProductActiveDataProvider;
use davidhirtz\yii2\shopify\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grid\GridView;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grid\StatusGridViewTrait;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\Timeago;
use Yii;

/**
 * Class ProductGridView
 * @package davidhirtz\yii2\shopify\modules\admin\widgets\grid\base
 * @property ProductActiveDataProvider $dataProvider
 */
class ProductGridView extends GridView
{
    use ModuleTrait;
    use StatusGridViewTrait;

    /**
     * @var bool whether product urls should be displayed in the name column
     */
    public $showUrl = false;

    /**
     * @return void
     */
    public function init()
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

        $this->initHeader();
        $this->initFooter();

        parent::init();
    }

    /**
     * Sets up grid header.
     */
    protected function initHeader()
    {
        if ($this->header === null) {
            $this->header = [
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
    }

    /**
     * Sets up grid footer.
     */
    protected function initFooter()
    {
        if ($this->footer === null) {
            $this->footer = [
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

    /**
     * @return array
     */
    public function nameColumn()
    {
        return [
            'attribute' => $this->getModel()->getI18nAttributeName('name'),
            'content' => function (Product $product) {
                $html = Html::markKeywords(Html::encode($product->getI18nAttribute('name')), $this->search);
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

    /**
     * @return array
     */
    public function totalInventoryQuantityColumn()
    {
        return [
            'attribute' => 'total_inventory_quantity',
            'headerOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'contentOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'content' => function (Product $product) {
                if ($product->variant->inventory_management ?? false) {
                    return Html::a(Yii::$app->getFormatter()->asInteger($product->total_inventory_quantity), $product->getAdminRoute(), [
                        'class' => 'badge',
                        'target' => '_blank',
                    ]);
                }

                return '-';
            }
        ];
    }

    /**
     * @return array
     */
    public function variantCountColumn()
    {
        return [
            'attribute' => 'variant_count',
            'headerOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'contentOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'content' => function (Product $product) {
                if ($product->variant_count > 1) {
                    $query = "admin/products/{$product->id}/variants/{$product->variant_id}";

                    return Html::a(Yii::$app->getFormatter()->asInteger($product->variant_count), static::getModule()->getShopUrl($query), [
                        'class' => 'badge',
                        'target' => '_blank',
                    ]);
                }

                return '';
            }
        ];
    }

    /**
     * @return array
     */
    public function updatedAtColumn()
    {
        return [
            'attribute' => 'updated_at',
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => function (Product $product) {
                return Timeago::tag($product->updated_at);
            }
        ];
    }

    /**
     * @return array
     */
    public function buttonsColumn()
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (Product $product) {
                return Html::buttons($this->getRowButtons($product));
            }
        ];
    }

    /**
     * @param Product $product
     * @return array
     */
    protected function getRowButtons(Product $product)
    {
        return [
            $this->getUpdateButton($product),
            $this->getShopifyAdminProductButton($product),
        ];
    }

    /**
     * @param Product $product
     * @return string
     */
    protected function getUrl($product): string
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

    /**
     * @return string
     */
    protected function getCreateProductButton()
    {
        return Html::a(Html::iconText('plus', Yii::t('shopify', 'New Product')), static::getModule()->getShopUrl('admin/products/new'), ['class' => 'btn btn-primary']);
    }

    /**
     * @return string
     */
    protected function getUpdateAllProductsButton()
    {
        return Html::a(Html::iconText('sync', Yii::t('shopify', 'Reload Products')), ['/admin/product/update-all'], [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    /**
     * @param Product $model
     * @return string
     */
    protected function getUpdateButton($model): string
    {
        return Html::a(Icon::tag('sync'), $this->getRoute($model), [
            'class' => 'btn btn-secondary',
            'data-method' => 'post',
        ]);
    }

    /**
     * @param Product $product
     * @return string
     */
    protected function getShopifyAdminProductButton($product): string
    {
        return Html::a(Icon::tag('wrench'), $product->getAdminRoute(), [
            'class' => 'btn btn-primary d-none d-md-inline-block',
            'target' => '_blank'
        ]);
    }


    /**
     * @return Product
     */
    public function getModel()
    {
        return Product::instance();
    }
}