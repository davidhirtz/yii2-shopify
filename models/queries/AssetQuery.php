<?php

namespace davidhirtz\yii2\shopify\models\queries;

use davidhirtz\yii2\shopify\models\Asset;
use davidhirtz\yii2\media\models\queries\FileQuery;
use davidhirtz\yii2\media\models\queries\FolderQuery;
use davidhirtz\yii2\skeleton\db\ActiveQuery;

/**
 * Class AssetQuery
 * @package davidhirtz\yii2\shopify\models\queries
 *
 * @method Asset one($db = null)
 */
class AssetQuery extends ActiveQuery
{
    /**
     * @return AssetQuery
     */
    public function selectSiteAttributes()
    {
        return $this->addSelect($this->prefixColumns(array_diff($this->getModelInstance()->attributes(),
            ['updated_by_user_id', 'created_at'])));
    }

    /**
     * @return AssetQuery
     */
    public function withFiles()
    {
        return $this->with([
            'files' => function (FileQuery $query) {
                $query->selectSiteAttributes()
                    ->replaceI18nAttributes()
                    ->with([
                        'folder' => function (FolderQuery $query) {
                            $query->selectSiteAttributes();
                        }
                    ]);
            }
        ]);
    }
}