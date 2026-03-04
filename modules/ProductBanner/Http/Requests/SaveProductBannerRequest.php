<?php

namespace Modules\ProductBanner\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\ProductBanner\Entities\ProductBanner;

class SaveProductBannerRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'product_banner::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'type' => ['required', Rule::in(ProductBanner::TYPES)],
            'placement' => ['nullable', Rule::in(['before_variations', 'after_variations'])],
            'hide_title' => 'nullable|boolean',
            'values' => 'array|min:1',
            'values.*.label' => 'required|distinct',
            'values.*.show_label' => 'nullable|boolean',
            'values.*.link_url' => 'nullable|url|max:1000',
            'values.*.color' => 'required_if:type,color|regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/',
            'values.*.image' => 'required_if:type,image',
            'values.*.design' => [
                'required_if:type,design',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $file = \Modules\Media\Entities\File::find($value);
                        if ($file && $file->size > 10485760) { // 10MB in bytes
                            $fail('The design file size must be less than 10MB.');
                        }
                    }
                },
            ],
        ];
    }


    public function __validationData(): array
    {
        return request()
            ->merge([
                'values' => $this->filter($this->values ?? []),
            ])
            ->all();
    }


    private function filter($values = [])
    {
        return array_filter($values, function ($value) {
            if (!array_has($value, 'label')) {
                return true;
            }

            return !is_null($value['label']);
        });
    }
}
