<?php

namespace davidhirtz\yii2\shopify\models\forms;

use davidhirtz\yii2\shopify\models\base\ProductImage;
use davidhirtz\yii2\shopify\models\base\ProductVariant;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;

/**
 * Class ProductShopifyAdminApiForm
 * @package davidhirtz\yii2\shopify\models\forms
 */
class ProductShopifyAdminApiForm extends Product
{
    /**
     * @param array $data
     * @return static
     */
    public static function loadOrCreateFromApiData($data)
    {
        $product = static::findOne($data['id']) ?? new static();
        $isNewRecord = $product->getIsNewRecord();

        $statusesMap = [
            'active' => static::STATUS_ENABLED,
            'draft' => static::STATUS_DRAFT,
            'archived' => static::STATUS_ARCHIVED,
        ];

        $product->status = $statusesMap[$data['status']];

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
            $image = $images[$imageData['id']] ?? new ProductImage();
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
            if (in_array($image->id, $imageIds)) {
                $image->delete();
            }
        }

        $product->image_id = $data['image']['id'] ?? null;
        $product->image_count = count($data['images']);

        // Variants.
        $variants = !$isNewRecord ? $product->variants : [];
        $variantIds = [];

        foreach ($data['variants'] as $variantData) {
            $variant = $variants[$variantData['id']] ?? new ProductVariant();
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
                'inventory_quantity' => 'inventory_quantity',
            ]);

            $variant->presentment_prices = count($variantData['presentment_prices']) > 1 ? json_encode($variantData['presentment_prices']) : null;

            if ($variant->save()) {
                if ($variant->position == 1) {
                    $product->variant_id = $variant->id;
                }

                $variantIds[] = $variant->id;
            } elseif ($errors = $variant->getErrors()) {
                $product->addErrors($errors);
                $variant->logErrors();
            }
        }

        foreach ($variants as $variant) {
            if (in_array($variant->id, $variantIds)) {
                $variant->delete();
            }
        }

        $product->variant_count = count($data['variants']);
        $product->updateAttributes(['image_id', 'image_count', 'variant_id', 'variant_count']);

        return $product;
    }

    /**
     * @param array $data
     * @return int
     */
    public static function deleteProductsFromApiData($data): int
    {
        $count = 0;

        if ($productIds = ArrayHelper::getColumn($data, 'id')) {
            $products = static::find()->where(['!=', 'id', $productIds]);

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
            $model->setAttribute($attributeName, $data[$key] ?? null);
        }

        $model->updated_at = new DateTime($data['updated_at']);
        $model->last_import_at = new DateTime();

        if ($model->getIsNewRecord()) {
            $model->created_at = new DateTime($data['created_at']);
        }
    }
}