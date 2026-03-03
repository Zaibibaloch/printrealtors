<?php

namespace Modules\ProductBanner\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBannerResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'is_global' => $this->is_global,
            'values' => ProductBannerValueResource::collection($this->values->sortBy('position')),
            'design_file' => $this->when($this->designFile, [
                'id' => $this->designFile->id ?? null,
                'path' => $this->designFile->path ?? null,
                'filename' => $this->designFile->filename ?? null,
            ]),
        ];
    }
}
