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

        foreach (array_reset_index($this->productBanners()) as $attributes) {
            if ($attributes['is_global'] === true) {
                $attributes['id'] = null;
            }

            $attributes['is_global'] = false;
            $attributes['position'] = ++$counter;

            $productBanner = $product->productBanners()->updateOrCreate(
                ['id' => $attributes['id'] ?? null],
                $attributes
            );

            $productBanner->saveValues($attributes['values'] ?? []);
        }
    }
}
