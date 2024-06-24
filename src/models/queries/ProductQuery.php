<?php

namespace davidhirtz\yii2\shopify\models\queries;

use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * @template T of Product
 * @extends ActiveQuery<T>
 */
class ProductQuery extends ActiveQuery
{
    public function matching(?string $search): static
    {
        if ($search = $this->sanitizeSearchString($search)) {
            $model = $this->getModelInstance();
            $tableName = $model::tableName();

            if (is_numeric($search)) {
                $this->andWhere("$tableName.[[id]] = :search OR $tableName.[[name]] LIKE :search", [
                    ':search' => "%$search%"
                ]);
            } else {
                $this->andWhere("$tableName.[[name]] LIKE :search", [
                    ':search' => "%$search%"
                ]);
            }
        }

        return $this;
    }
}
