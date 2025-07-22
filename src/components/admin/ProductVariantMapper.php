<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

use davidhirtz\yii2\shopify\components\ShopifyDateTime;
use davidhirtz\yii2\shopify\components\ShopifyId;
use davidhirtz\yii2\shopify\components\ShopifyPrice;
use davidhirtz\yii2\shopify\models\Product;
use davidhirtz\yii2\shopify\models\ProductVariant;
use Yii;

readonly class ProductVariantMapper
{
    protected ProductVariant $variant;

    public function __construct(protected Product $product, protected array $data)
    {
        $id = (new ShopifyId($data['id']))->toInt();

        $this->variant = ProductVariant::findOne($id) ?? ProductVariant::create();

        $this->variant->id = $id;
        $this->variant->populateProductRelation($this->product);

        $this->setAttributes();
    }

    protected function setAttributes(): void
    {
        $imageId = $this->data['media']['nodes'][0]['id'] ?? null;
        $this->variant->image_id = $imageId ? (new ShopifyId($imageId))->toInt() : null;

        $this->variant->price = (new ShopifyPrice($this->data['price']))->toInt();
        $this->variant->compare_at_price = $this->data['compareAtPrice']
            ? (new ShopifyPrice($this->data['compareAtPrice']))->toInt()
            : null;

        $this->variant->name = $this->data['title'];
        $this->variant->sku = $this->data['sku'] ?: null;
        $this->variant->barcode = $this->data['barcode'] ?: null;
        $this->variant->inventory_policy = $this->data['inventoryPolicy'] ?? null;
        $this->variant->inventory_quantity = $this->data['inventoryQuantity'];
        $this->variant->inventory_tracked = $this->data['inventoryItem']['tracked'];
        $this->variant->is_taxable = $this->data['taxable'] ?? false;
        $this->variant->weight = $this->data['inventoryItem']['measurement']['weight']['value'] ?? null;

        $this->variant->weight_unit = match ($this->data['inventoryItem']['measurement']['weight']['unit'] ?? null) {
            'KILOGRAMS' => 'KG',
            'GRAMS' => 'G',
            'OUNCES' => 'OZ',
            'POUNDS' => 'LB',
            default => null,
        };

        $pricePerUnit = $this->data['unitPrice']['amount'] ?? null;
        $price = $pricePerUnit ? new ShopifyPrice($pricePerUnit, $this->data['unitPrice']['currencyCode']) : null;

        $this->variant->unit_price = $price?->toInt();
        $this->variant->unit_price_measurement = $this->data['unitPriceMeasurement']['referenceUnit'] ?? null;

        $keys = array_column($this->product->options ?? [], 'name');
        $selectedValues = [];

        foreach ($this->data['selectedOptions'] as $option) {
            $key = array_search($option['name'], $keys);

            if ($key !== false) {
                $selectedValues[$key] = $option['value'];
            }
        }

        $this->variant->option_1 = $selectedValues[0] ?? null;
        $this->variant->option_2 = $selectedValues[1] ?? null;
        $this->variant->option_3 = $selectedValues[2] ?? null;

        $this->variant->updated_at = (new ShopifyDateTime($this->data['updatedAt']))->toDateTime();
        $this->variant->created_at = (new ShopifyDateTime($this->data['createdAt']))->toDateTime();
    }

    public function __invoke(): ProductVariant
    {
        return $this->variant;
    }
}
