<?php

namespace davidhirtz\yii2\shop;

use davidhirtz\yii2\shop\modules\GatewayModuleInterface;
use davidhirtz\yii2\skeleton\modules\ModuleTrait;

/**
 * Class Module
 * @package davidhirtz\yii2\shop
 */
class Module extends \yii\base\Module
{
    use ModuleTrait;

    public function init()
    {
        dump($this->getModules());
        parent::init();
    }

    /**
     * @return GatewayModuleInterface
     */
    public function getGateway()
    {
        /** @var GatewayModuleInterface $module */
        $module = $this->getModule('gateway');
        return $module;
    }
}