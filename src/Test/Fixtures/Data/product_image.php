<?php

declare(strict_types=1);

use yii\db\Expression;

return [
    'image-1-1' => [
        'id' => 1,
        'product_id' => 1,
        'position' => 1,
        'alt_text' => 'Front view of sample t-shirt',
        'width' => 800,
        'height' => 600,
        'src' => 'https://cdn.shopify.com/s/files/1/0000/0000/products/sample-tshirt-front.jpg',
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'image-1-2' => [
        'id' => 2,
        'product_id' => 1,
        'position' => 2,
        'alt_text' => 'Back view of sample t-shirt',
        'width' => 800,
        'height' => 600,
        'src' => 'https://cdn.shopify.com/s/files/1/0000/0000/products/sample-tshirt-back.jpg',
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'image-2-1' => [
        'id' => 3,
        'product_id' => 2,
        'position' => 1,
        'alt_text' => 'Sample coffee mug',
        'width' => 600,
        'height' => 600,
        'src' => 'https://cdn.shopify.com/s/files/1/0000/0000/products/sample-mug.jpg',
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];