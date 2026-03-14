<?php

namespace Modules\Product\Listeners;

use Modules\Product\Entities\Product;

class SaveProductBanners
{
    /**
     * Handle the event.
     */
    public function handle(Product $product): void
    {
        $ids = $this->getDeleteCandidates($product);

        if ($ids->isNotEmpty()) {
            $product->productBanners()->detach($ids);
        }

        $this->saveProductBanners($product);
    }


    private function getDeleteCandidates($product)
    {
        return $product
            ->productBanners()
            ->pluck('id')
            ->diff(array_pluck($this->productBanners(), 'id'));
    }


    private function productBanners()
    {
        return array_filter(request('product_banners', []), function ($productBanner) {
            return !is_null($productBanner['name']);
        });
    }


    private function saveProductBanners($product): void
    {
        $counter = 0;
        $savedBannerIds = [];

        foreach (array_reset_index($this->productBanners()) as $attributes) {
            if ($attributes['is_global'] === true) {
                $attributes['id'] = null;
            }

            $attributes['is_global'] = false;
            $attributes['position'] = ++$counter;
            // Unchecked checkboxes are not sent; default to false so labels show on storefront
            $attributes['hide_value_labels'] = isset($attributes['hide_value_labels']) ? (bool) $attributes['hide_value_labels'] : false;

            $productBanner = $product->productBanners()->updateOrCreate(
                ['id' => $attributes['id'] ?? null],
                $attributes
            );

            $productBanner->saveValues($attributes['values'] ?? []);
            $savedBannerIds[] = $productBanner->id;
        }

        // Ensure all saved banners are attached to the product (pivot); new banners created
        // via updateOrCreate are not auto-attached by the relation.
        $product->productBanners()->sync($savedBannerIds);
    }
}
