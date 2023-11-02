<?php

namespace davidhirtz\yii2\shopify\models\queries;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * Class ProductQuery
 * @package davidhirtz\yii2\shopify\models\queries
 *
 * @method Product one($db = null)
 * @method Product[] all($db = null)
 */
class ProductQuery extends ActiveQuery
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

            if (is_numeric($search)) {
                $this->andWhere("{$tableName}.[[id]] = :search OR {$tableName}.[[name]] LIKE :search", [
                    ':search' => "%{$search}%"
                ]);
            } else {
                $this->andWhere("{$tableName}.[[name]] LIKE :search", [
                    ':search' => "%{$search}%"
                ]);
            }
        }

        return $this;
    }
}