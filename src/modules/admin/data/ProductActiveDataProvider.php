<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Modules\Admin\Data;

use Hirtz\Shopify\models\Product;
use Hirtz\Shopify\models\queries\ProductQuery;
use yii\data\ActiveDataProvider;

/**
 * @property ProductQuery|null $query
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

    #[\Override]
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
