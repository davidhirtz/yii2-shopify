<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\tests\fixtures;

use davidhirtz\yii2\skeleton\models\User;
use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = User::class;
}
