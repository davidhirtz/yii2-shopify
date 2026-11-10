<?php

declare(strict_types=1);

namespace Hirtz\Shopify\Widgets\StructuredData;

use Hirtz\Shopify\Models\Product as ShopifyProduct;
use Hirtz\Shopify\Models\ProductVariant;
use Hirtz\Skeleton\Widgets\StructuredData\Thing;
use Override;

/**
 * Each variant is an `Offer` of its own, since Google's merchant listings refuse an `AggregateOffer`.
 */
class Product extends Thing
{
    protected ?string $type = 'Product';
    protected ?ShopifyProduct $product = null;
    protected ?string $url = null;

    public function product(?ShopifyProduct $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function url(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    #[Override]
    public function isVisible(): bool
    {
        return $this->product?->name
            && $this->product->variants
            && parent::isVisible();
    }

    #[Override]
    protected function getProperties(): array
    {
        if ($this->product === null) {
            return parent::getProperties();
        }

        $variants = array_values($this->product->variants);
        $offers = array_map($this->getOffer(...), $variants);

        return [
            'name' => $this->product->name,
            'description' => $this->getDescription(),
            'image' => array_values(array_filter(array_map(
                fn ($image): ?string => $image->src,
                $this->product->images,
            ))) ?: null,
            'brand' => $this->product->vendor ? ['@type' => 'Brand', 'name' => $this->product->vendor] : null,
            'url' => $this->url,
            'offers' => count($offers) === 1 ? $offers[0] : $offers,
            ...parent::getProperties(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOffer(ProductVariant $variant): array
    {
        return [
            '@type' => 'Offer',
            'name' => $this->product?->variant_count > 1 ? $variant->name : null,
            'sku' => $variant->sku,
            'price' => number_format($variant->price / 100, 2, '.', ''),
            'priceCurrency' => ShopifyProduct::getShopify()->defaultCurrency,
            'availability' => 'https://schema.org/' . ($this->isInStock($variant) ? 'InStock' : 'OutOfStock'),
            'url' => $this->url,
        ];
    }

    protected function isInStock(ProductVariant $variant): bool
    {
        return !$variant->inventory_tracked
            || $variant->inventory_quantity > 0
            || strtoupper((string)$variant->inventory_policy) === 'CONTINUE';
    }

    protected function getDescription(): ?string
    {
        $text = html_entity_decode(strip_tags((string)$this->product?->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string)preg_replace('/\s+/u', ' ', $text)) ?: null;
    }
}
