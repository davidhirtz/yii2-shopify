<?php

namespace PHPSTORM_META {

    override(
        \yii\base\Module::get(0),
        map([
            'shopify' => '\davidhirtz\yii2\shopify\components\ShopifyComponent',
        ])
    );
}
