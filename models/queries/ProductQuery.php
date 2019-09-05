<?php

namespace davidhirtz\yii2\shop\models\queries;

use davidhirtz\yii2\shop\models\Product;

/**
 * Class ProductQuery
 * @package davidhirtz\yii2\shop\models\queries
 *
 * @method Product one($db = null)
 */
class ProductQuery extends \davidhirtz\yii2\skeleton\db\ActiveQuery
{
    /**
     * @param string $search
     * @return $this
     */
    public function matching($search)
    {
        if ($search = $this->sanitizeSearchString($search)) {
            $model = $this->getModelInstance();
            $tableName = $model::tableName();

            $this->andWhere("{$tableName}.[[name]] LIKE :search", [':search' => "%{$search}%"]);
        }

        return $this;
    }
}