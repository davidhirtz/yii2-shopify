<?php

declare(strict_types=1);

use yii\db\Expression;

return [
    'variant-1-1' => [
        'id' => 1,
        'product_id' => 1,
        'name' => 'Small / Red',
        'position' => 1,
        'price' => 1999, // In cents
        'compare_at_price' => 2499,
        'option_1' => 'Small',
        'option_2' => 'Red',
        'sku' => 'PROD1-SM-RED',
        'barcode' => '123456789012',
        'is_taxable' => true,
        'inventory_quantity' => 10,
        'inventory_policy' => 'deny',
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'variant-1-2' => [
        'id' => 2,
        'product_id' => 1,
        'name' => 'Medium / Blue',
        'position' => 2,
        'price' => 2199,
        'option_1' => 'Medium',
        'option_2' => 'Blue',
        'sku' => 'PROD1-MD-BLU',
        'is_taxable' => true,
        'inventory_quantity' => 5,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'variant-2-1' => [
        'id' => 3,
        'product_id' => 2,
        'name' => 'Default Variant',
        'position' => 1,
        'price' => 2999,
        'sku' => 'PROD2-DEF',
        'is_taxable' => false,
        'inventory_quantity' => 0,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
