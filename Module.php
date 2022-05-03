<?php

namespace davidhirtz\yii2\shopify;

use davidhirtz\yii2\skeleton\modules\ModuleTrait;
use yii\base\InvalidConfigException;

/**
 * Class Module
 * @package davidhirtz\yii2\shopify
 */
class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @var string
     */
    public $tablePrefix = 'shopify_';

    /**
     * @return void
     */
    public function init()
    {
        if ($this->enableI18nTables) {
            throw new InvalidConfigException('The shopify module does not support I18N database tables.');
        }

        parent::init();
    }
}