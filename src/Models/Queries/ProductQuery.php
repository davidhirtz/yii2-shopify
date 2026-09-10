<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Models\Queries;

use Hirtz\Shopify\Models\Product;
use Hirtz\Skeleton\Db\I18nActiveQuery;

/**
 * @template T of Product
 * @template-extends I18nActiveQuery<T>
 */
class ProductQuery extends I18nActiveQuery
{
    public function matching(?string $search): static
    {
        if ($search = $this->sanitizeSearchString($search)) {
            $model = $this->getModelInstance();
            $tableName = $model::tableName();

            if (is_numeric($search)) {
                $this->andWhere("$tableName.[[id]] = :id OR $tableName.[[name]] LIKE :search", [
                    ':id' => (int)$search,
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
