<?php

namespace davidhirtz\yii2\shopify\modules\admin\data;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use yii\data\ActiveDataProvider;

/**
 * @property ProductQuery $query
 */
class ProductActiveDataProvider extends ActiveDataProvider
{
    /**
     * @var int|null the product status
     */
    public ?int $status = null;

    /**
     * @var string|null the text search
     */
    public ?string $searchString = null;

    public function init(): void
    {
        $this->query = $this->query ?: Product::find();
        $this->initQuery();

        parent::init();
    }

    protected function initQuery(): void
    {
        $this->query->with(['image', 'variant']);

        if ($this->status !== null) {
            $this->query->andWhere([Product::tableName() . '.[[status]]' => $this->status]);
        }

        if ($this->searchString) {
            $this->query->matching($this->searchString);
        }
    }
}