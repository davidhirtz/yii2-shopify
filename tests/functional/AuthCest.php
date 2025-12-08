<?php

declare(strict_types=1);

/**
 * @noinspection PhpUnused
 */

namespace Hirtz\Shopify\tests\functional;

use Hirtz\Shopify\models\Product;
use Hirtz\Shopify\modules\admin\data\ProductActiveDataProvider;
use Hirtz\Shopify\modules\admin\widgets\grids\ProductGridView;
use Hirtz\Shopify\tests\support\FunctionalTester;
use Hirtz\Skeleton\codeception\fixtures\UserFixtureTrait;
use Hirtz\Skeleton\codeception\functional\BaseCest;
use Hirtz\Skeleton\models\User;
use Hirtz\Skeleton\modules\admin\widgets\forms\LoginActiveForm;
use Yii;

class AuthCest extends BaseCest
{
    use UserFixtureTrait;

    public function checkIndexAsGuest(FunctionalTester $I): void
    {
        $I->amOnPage('/admin/product/index');

        $widget = Yii::createObject(LoginActiveForm::class);
        $I->seeElement("#$widget->id");
    }

    public function checkIndexWithoutPermission(FunctionalTester $I): void
    {
        $this->getLoggedInUser();

        $I->amOnPage('/admin/product/index');
        $I->seeResponseCodeIs(403);
    }

    public function checkIndexWithPermission(FunctionalTester $I): void
    {
        $user = $this->getLoggedInUser();
        $auth = Yii::$app->getAuthManager()->getPermission(Product::AUTH_PRODUCT_UPDATE);
        Yii::$app->getAuthManager()->assign($auth, $user->id);

        $I->amOnPage('/admin/product/index');

        $widget = Yii::$container->get(ProductGridView::class, [], [
            'dataProvider' => Yii::createObject(ProductActiveDataProvider::class),
        ]);

        $I->seeElement("#$widget->id");
    }

    protected function getLoggedInUser(): User
    {
        $user = user::find()->one();

        $webuser = Yii::$app->getUser();
        $webuser->loginType = 'test';
        $webuser->login($user);

        return $user;
    }
}
