<?php

declare(strict_types=1);


use yii\db\Expression;

return [
    'variant-1-1' => [
        'id' => 1,
        'product_id' => 1,
        'image_id' => null,
        'name' => 'Test Variant 1',
        'position' => 1,
        'price' => 1999,
        'compare_at_price' => 2499,
        'option_1' => 'XS',
        'inventory_quantity' => 100,
        'inventory_tracked' => true,
        'unit_price' => 99,
        'unit_price_measurement' => 'KG',
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'variant-1-2' => [
        'id' => 2,
        'product_id' => 1,
        'image_id' => null,
        'name' => 'Test Variant 1',
        'position' => 2,
        'price' => 1999,
        'compare_at_price' => 2499,
        'option_1' => 'SM',
        'inventory_quantity' => 100,
        'inventory_tracked' => true,
        'unit_price' => 99,
        'unit_price_measurement' => 'KG',
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];