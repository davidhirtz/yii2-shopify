<?php

namespace davidhirtz\yii2\shopify\models\forms;

use DateTimeZone;
use davidhirtz\yii2\shopify\models\ProductImage;
use davidhirtz\yii2\shopify\models\ProductVariant;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use davidhirtz\yii2\skeleton\log\ActiveRecordErrorLogger;
use Yii;
use yii\helpers\Json;

class ProductShopifyAdminRestApiForm
{
    public static function createOrUpdateFromApiData(array $data): ?Product
    {
        $product = Product::findOne($data['id']) ?? Product::create();
        $isNewRecord = $product->getIsNewRecord();

        $statusesMap = [
            'active' => Product::STATUS_ENABLED,
            'draft' => Product::STATUS_DRAFT,
            'archived' => Product::STATUS_ARCHIVED,
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

        if (count($data['options']) !== 1 || $data['options'][0]['name'] !== 'Title' ||
            count($data['options'][0]['values']) !== 1 || $data['options'][0]['values'][0] !== 'Default Title') {
            foreach ($data['options'] as $option) {
                $options[$option['position']] = [
                    'name' => $option['name'],
                    'values' => $option['values'],
                ];
            }
        }

        ksort($options);
        $product->options = $options ? Json::encode($options) : null;

        if (!$product->save()) {
            ActiveRecordErrorLogger::log($product);
            return $product;
        }

        // Images.
        $images = !$isNewRecord ? $product->images : [];
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

        // Variants.
        $variants = !$isNewRecord ? $product->variants : [];
        $totalInventoryCount = 0;
        $variantIds = [];

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

            $variant->presentment_prices = count($variantData['presentment_prices'] ?? []) > 1
                ? Json::encode($variantData['presentment_prices'])
                : null;

            if ($variant->save()) {
                if ($variant->position == 1) {
                    $product->variant_id = $variant->id;
                }

                if ($variant->inventory_management) {
                    $totalInventoryCount += $variant->inventory_quantity;
                }

                $variantIds[] = $variant->id;
            } elseif ($errors = $variant->getErrors()) {
                ActiveRecordErrorLogger::log($variant);
                $product->addErrors($errors);
            }
        }

        foreach ($variants as $variant) {
            if (!in_array($variant->id, $variantIds)) {
                $variant->delete();
            }
        }

        $product->total_inventory_quantity = $totalInventoryCount;
        $product->variant_count = count($data['variants']);
        $product->update();

        return $product;
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