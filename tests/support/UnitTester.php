<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\support;

use Codeception\Actor;
use davidhirtz\yii2\shopify\models\Product;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends Actor
{
    use _generated\UnitTesterActions;

    public function getProductFixture(string $index = 'product-1'): Product
    {
        return $this->grabFixture('products', $index);
    }
}
