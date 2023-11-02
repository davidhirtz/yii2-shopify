<?php

namespace davidhirtz\yii2\shopify\models\forms;

use DateTimeZone;
use davidhirtz\yii2\shopify\models\base\ProductImage;
use davidhirtz\yii2\shopify\models\base\ProductVariant;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use Yii;

/**
 * Class ProductShopifyAdminApiForm
 * @package davidhirtz\yii2\shopify\models\forms
 */
class ProductShopifyAdminRestApiForm
{
    /**
     * @param array $data
     * @return Product
     */
    public static function createOrUpdateFromApiData($data)
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
        $product->options = $options ? json_encode($options) : null;

        if (!$product->save()) {
            $product->logErrors();
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
                $product->addErrors($errors);
                $image->logErrors();
            }
        }

        foreach ($images as $image) {
            if (!in_array($image->id, $imageIds)) {
                $image->delete();
            }
        }

        $product->image_id = isset($data['image']['id']) ? (string)$data['image']['id'] : null;
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

            $variant->presentment_prices = count($variantData['presentment_prices'] ?? []) > 1 ? json_encode($variantData['presentment_prices']) : null;

            if ($variant->save()) {
                if ($variant->position == 1) {
                    $product->variant_id = $variant->id;
                }

                if ($variant->inventory_management) {
                    $totalInventoryCount += $variant->inventory_quantity;
                }

                $variantIds[] = $variant->id;
            } elseif ($errors = $variant->getErrors()) {
                $product->addErrors($errors);
                $variant->logErrors();
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

    /**
     * @param array $results
     * @return int
     */
    public static function deleteProductsFromApiResult($results): int
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

    /**
     * @param Product|ProductImage|ProductVariant $model
     * @param array $data
     * @param array $attributesMap
     * @return void
     */
    private static function setAttributesFromApiData($model, $data, $attributesMap)
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