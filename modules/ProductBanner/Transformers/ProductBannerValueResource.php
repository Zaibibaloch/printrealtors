<?php

namespace Modules\ProductBanner\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBannerValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'label' => $this->label,
            'show_label' => $this->show_label,
            'link_url' => $this->link_url,
            'image' => $this->when(
                condition: $this->product_banner->type === 'image',
                value: fn () => [
                    'id' => $this->image?->id,
                    'path' => $this->image?->path,
                ]
            ),
            'design' => $this->when(
                condition: $this->product_banner->type === 'design',
                value: fn () => [
                    'id' => $this->design?->id,
                    'path' => $this->design?->path,
                ]
            ),
            'color' => $this->when(
                condition: $this->product_banner->type === 'color',
                value: $this->color
            ),
        ];
    }
}
