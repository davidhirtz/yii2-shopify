<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\modules\admin\data;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\queries\ProductQuery;
use Override;
use yii\data\ActiveDataProvider;

/**
 * @property ProductQuery|null $query
 */
class ProductActiveDataProvider extends ActiveDataProvider
{
    public ?int $status = null;
    public ?string $searchString = null;

    #[Override]
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

    #[Override]
    public function setSort($value): void
    {
        if (is_array($value)) {
            $value['defaultOrder'] ??= ['last_import_at' => SORT_DESC];
        }

        parent::setSort($value);
    }
}
