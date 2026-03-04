<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Option\Entities\Option;
use Modules\Product\Entities\Product;
use Modules\Core\Http\Requests\Request;
use Modules\ProductBanner\Entities\ProductBanner;
use Modules\Variation\Entities\Variation;
use Modules\Product\Rules\DistinctProductVariationValueLabel;

class SaveProductRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'product::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            $this->getProductRules(),
            $this->getProductAttributeRules(),
            $this->getProductVariationsRules(),
            $this->getProductBannerRules(),
            $this->getProductVariantsRules(),
            $this->getProductOptionsRules(),
        );
    }


    public function getProductRules(): array
    {
        return array_merge(
            [
                'slug' => $this->getSlugRules(),
                'name' => 'required',
                'description' => 'required',
                // Keep support for a single primary brand_id for backwards compatibility
                'brand_id' => ['nullable', Rule::exists('brands', 'id')],
                // New: allow assigning multiple brands, similar to categories
                'brands' => ['nullable', 'array'],
                'brands.*' => [Rule::exists('brands', 'id')],
                'tax_class_id' => ['nullable', Rule::exists('tax_classes', 'id')],
                'price' => 'required_without:variants|nullable|numeric|min:0|max:99999999999999',
                'special_price' => 'nullable|numeric|min:0|max:99999999999999',
                'special_price_type' => ['nullable', Rule::in(['fixed', 'percent'])],
                'special_price_start' => 'nullable|date|before:special_price_end',
                'special_price_end' => 'nullable|date|after:special_price_start',
                'manage_stock' => 'required|boolean',
                'qty' => 'required_if:manage_stock,1|nullable|numeric',
                'in_stock' => 'required|boolean',
                'new_from' => 'nullable|date',
                'new_to' => 'nullable|date',
                'is_virtual' => 'required|boolean',
                'is_active' => 'required|boolean',
            ],
            $this->getInventoryRules()
        );
    }


    public function getInventoryRules(): array
    {
        if (!$this->request->has('variations')) {
            return [
                'manage_stock' => 'required|boolean',
                'qty' => 'required_if:manage_stock,1|nullable|numeric',
                'in_stock' => 'required|boolean',
            ];
        }

        return [];
    }


    public function getProductAttributeRules(): array
    {
        return [
            'attributes.*.attribute_id' => ['required_with:attributes.*.values', Rule::exists('attributes', 'id')],
            'attributes.*.values' => ['required_with:attributes.*.attribute_id', Rule::exists('attribute_values', 'id')],
        ];
    }


    public function getProductVariationsRules(): array
    {
        return [
            'variations.*.name' => 'required_with:variations.*.type',
            'variations.*.type' => ['nullable', 'required_with:variations.*.name', Rule::in(Variation::TYPES)],
            'variations.*.values.*.label' => ['required_with:variations.*.type', new DistinctProductVariationValueLabel()],
            'variations.*.values.*.color' => ['required_if:variations.*.type,color', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'variations.*.values.*.image' => ['required_if:variations.*.type,image', 'integer', 'min:1'],
        ];
    }


    public function getProductBannerRules(): array
    {
        return [
            'product_banners.*.name' => 'required_with:product_banners.*.type',
            'product_banners.*.type' => ['nullable', 'required_with:product_banners.*.name', Rule::in(ProductBanner::TYPES)],
            'product_banners.*.placement' => ['nullable', Rule::in(['before_variations', 'after_variations'])],
            'product_banners.*.hide_title' => ['nullable', 'boolean'],
            'product_banners.*.values.*.label' => ['required_with:product_banners.*.type', 'distinct'],
            'product_banners.*.values.*.show_label' => ['nullable', 'boolean'],
            'product_banners.*.values.*.link_url' => ['nullable', 'url', 'max:1000'],
            'product_banners.*.values.*.color' => ['required_if:product_banners.*.type,color', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'product_banners.*.values.*.image' => ['required_if:product_banners.*.type,image', 'integer', 'min:1'],
            'product_banners.*.values.*.design' => ['required_if:product_banners.*.type,design', 'nullable', 'integer', 'min:1'],
        ];
    }


    public function getProductVariantsRules(): array
    {
        return [
            'variants.*.name' => 'required',
            'variants.*.sku' => 'nullable',
            'variants.*.price' => 'required_if:variants.*.is_active,1|nullable|numeric|min:0|max:99999999999999',
            'variants.*.special_price' => 'nullable|numeric|min:0|max:99999999999999',
            'variants.*.special_price_type' => ['nullable', Rule::in(['fixed', 'percent'])],
            'variants.*.special_price_start' => 'nullable|date|before:variants.*.special_price_end',
            'variants.*.special_price_end' => 'nullable|date|after:variants.*.special_price_start',
            'variants.*.manage_stock' => 'required_if:variants.*.is_active,1|boolean',
            'variants.*.qty' => 'required_if:variants.*.is_active,1|required_if:variants.*.manage_stock,1|nullable|numeric',
            'variants.*.in_stock' => 'required_if:variants.*.is_active,1|boolean',
            'variants.*.is_active' => 'required|boolean',
        ];
    }


    public function getProductOptionsRules(): array
    {
        return [
            'options.*.name' => 'required_with:options.*.type',
            'options.*.type' => ['nullable', 'required_with:options.*.name', Rule::in(Option::TYPES)],
            'options.*.is_required' => ['required_with:options.*.name', 'boolean'],
            'options.*.values.*.label' => 'required_if:options.*.type,dropdown,checkbox,checkbox_custom,radio,radio_custom,multiple_select',
            'options.*.values.*.price' => 'nullable|numeric|min:0|max:99999999999999',
            'options.*.values.*.price_type' => ['required', Rule::in(['fixed', 'percent'])],
        ];
    }


    public function messages()
    {
        return array_merge(parent::messages(), [
            'price.required_without' => trans('product::validation.price_field_is_required'),
        ]);
    }


    private function getSlugRules(): array
    {
        $rules = $this->route()->getName() === 'admin.products.update' ? ['required'] : ['sometimes'];

        $slug = Product::withoutGlobalScope('active')
            ->where('id', $this->id)
            ->value('slug');

        $rules[] = Rule::unique('products', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
