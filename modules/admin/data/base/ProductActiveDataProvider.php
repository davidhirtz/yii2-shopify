<?php

namespace davidhirtz\yii2\shopify\modules\admin\data\base;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use yii\data\ActiveDataProvider;

/**
 * Class ProductActiveDataProvider
 * @package davidhirtz\yii2\shopify\modules\admin\data\base
 * @property ProductQuery $query
 */
class ProductActiveDataProvider extends ActiveDataProvider
{
    /**
     * @var string
     */
    public $searchString;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->query = $this->query ?: Product::find();
        $this->initQuery();

        parent::init();
    }

    /**
     * Inits query
     */
    protected function initQuery()
    {
        $this->query->with('variant');

        if ($this->searchString) {
            $this->query->matching($this->searchString);
        }
    }
}