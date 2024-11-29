<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\models\forms;

use DateTimeZone;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductImage;
use davidhirtz\yii2\shopify\models\ProductVariant;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;
use Yii;

class ProductShopifyAdminRestApiForm
{
    public static function createOrUpdateFromApiData(array $data): ?Product
    {
        $product = Product::findOne($data['id']) ?? Product::create();

        $statusesMap = [
            'active' => $product::STATUS_ENABLED,
            'draft' => $product::STATUS_DRAFT,
            'archived' => $product::STATUS_ARCHIVED,
        ];

        $product->status = $statusesMap[$data['status']];
        $product->last_import_at = new DateTime();

        static::setAttributesFromApiData($product, $data, [
            'id' => 'id',
            'title' => 'name',
            'body_html' => 'content',
            'handle' => 'slug',
            'tags' => 'tags',
            'vendor' => 'vendor',
            'product_type' => 'product_type',
        ]);

        $options = [];

        if (count($data['options']) !== 1
            || $data['options'][0]['name'] !== 'Title'
            || count($data['options'][0]['values']) !== 1
            || $data['options'][0]['values'][0] !== 'Default Title'
        ) {
            foreach ($data['options'] as $option) {
                $options[$option['position']] = [
                    'name' => $option['name'],
                    'values' => $option['values'],
                ];
            }
        }

        ksort($options);
        $product->options = $options ?: null;

        if (!$product->save()) {
            ActiveRecordErrorLogger::log($product);
            return $product;
        }

        static::saveImagesFromApiData($product, $data);
        static::saveVariantsFromApiData($product, $data);

        $product->update();

        return $product;
    }

    protected static function saveImagesFromApiData(Product $product, array $data): void
    {
        $images = $product->image_count ? $product->images : [];
        $imageIds = [];

        foreach ($data['images'] as $imageData) {
            $image = $images[$imageData['id']] ?? ProductImage::create();
            $image->populateProductRelation($product);

            static::setAttributesFromApiData($image, $imageData, [
                'id' => 'id',
                'position' => 'position',
                'alt' => 'alt_text',
                'width' => 'width',
                'height' => 'height',
                'src' => 'src',
            ]);

            if ($image->save()) {
                $imageIds[] = $image->id;
            } elseif ($errors = $image->getErrors()) {
                ActiveRecordErrorLogger::log($image);
                $product->addErrors($errors);
            }
        }

        foreach ($images as $image) {
            if (!in_array($image->id, $imageIds)) {
                $image->delete();
            }
        }

        $product->image_id = $data['image']['id'] ?? null;
        $product->image_count = count($data['images']);
    }

    protected static function saveVariantsFromApiData(Product $product, array $data): void
    {
        $variants = $product->variant_count ? $product->variants : [];
        $totalInventoryCount = 0;
        $variantIds = [];

        $product->variant_id = null;

        foreach ($data['variants'] as $variantData) {
            $variant = $variants[$variantData['id']] ?? ProductVariant::create();
            $variant->populateProductRelation($product);

            static::setAttributesFromApiData($variant, $variantData, [
                'id' => 'id',
                'image_id' => 'image_id',
                'title' => 'name',
                'price' => 'price',
                'compare_at_price' => 'compare_at_price',
                'sku' => 'sku',
                'position' => 'position',
                'option1' => 'option_1',
                'option2' => 'option_2',
                'option3' => 'option_3',
                'taxable' => 'is_taxable',
                'barcode' => 'barcode',
                'grams' => 'grams',
                'weight' => 'weight',
                'weight_unit' => 'weight_unit',
                'inventory_management' => 'inventory_management',
                'inventory_quantity' => 'inventory_quantity',
            ]);

            $variant->presentment_prices = $variantData['presentment_prices'] ?? null;

            if ($variant->inventory_quantity < 0) {
                $variant->inventory_quantity = null;
            }

            if ($variant->save()) {
                if ($variant->inventory_management && $variant->inventory_quantity !== null) {
                    $totalInventoryCount += $variant->inventory_quantity;
                }

                $variantIds[] = $variant->id;
            } elseif ($errors = $variant->getErrors()) {
                ActiveRecordErrorLogger::log($variant);
                $product->addErrors($errors);
            }
        }

        foreach ($variants as $key => $variant) {
            if (!in_array($variant->id, $variantIds)) {
                $variant->delete();
                unset($variants[$key]);
            }
        }

        $firstVariant = array_reduce($variants, function ($carry, ProductVariant $variant) {
            if ($carry === null || $variant->position < $carry->position) {
                $carry = $variant;
            }

            return $carry;
        });

        $product->variant_id = $firstVariant->id ?? null;
        $product->total_inventory_quantity = $totalInventoryCount;
        $product->variant_count = count($data['variants']);
    }

    public static function deleteProductsFromApiResult(array $results): int
    {
        $count = 0;

        if ($productIds = ArrayHelper::getColumn($results, 'id')) {
            $products = Product::find()
                ->where(['not in', 'id', $productIds])
                ->all();

            foreach ($products as $product) {
                if ($product->delete()) {
                    $count++;
                }
            }
        }

        return $count;
    }

    protected static function setAttributesFromApiData(Product|ProductImage|ProductVariant $model, array $data, array $attributesMap): void
    {
        foreach ($attributesMap as $key => $attributeName) {
            $model->setAttribute($attributeName, $data[$key] ?: null);
        }

        $model->updated_at = new DateTime($data['updated_at']);
        $model->updated_at->setTimezone(new DateTimeZone(Yii::$app->getTimeZone()));

        if ($model->getIsNewRecord()) {
            $model->created_at = new DateTime($data['created_at']);
        }
    }
}
