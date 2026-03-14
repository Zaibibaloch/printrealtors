<?php

namespace Modules\ProductBanner\Entities;

use Illuminate\Support\Collection;
use Modules\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Modules\Support\Eloquent\Translatable;
use Modules\ProductBanner\Admin\ProductBannerTable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBanner extends Model
{
    use Translatable, SoftDeletes;

    /**
     * Available product_banner types.
     *
     * @var array
     */
    const TYPES = ['text', 'color', 'image', 'design'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations', 'values'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'type',
        'is_global',
        'position',
        'placement',
        'hide_title',
        'hide_value_labels',
        'design_file_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_global' => 'boolean',
        'hide_title' => 'boolean',
        'hide_value_labels' => 'boolean',
        'deleted_at' => 'datetime'
    ];


    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatedAttributes = ['name'];


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saved(function ($product_banner) {
            if (request()->routeIs('admin.product_banners.*')) {
                $product_banner->saveValuesForGlobal();
            }

            if (request()->routeIs('admin.products.*')) {
                $product_banner->saveValuesForLocal();
            }
        });
    }


    /**
     * Save values for the product_banner.
     *
     * @param array $values
     *
     * @return void
     */
    public function saveValues(array $values = []): void
    {
        $ids = $this->getDeleteCandidates($values);

        if ($ids->isNotEmpty()) {
            $this->values()
                ->whereIn('id', $ids)
                ->delete();
        }

        $counter = 0;

        foreach (array_reset_index($values) as $attributes) {
            $attributes += ['position' => ++$counter];
            $attributes += ['value' => $attributes['color'] ?? ''];
            // Unchecked checkboxes are not sent; default to true so labels show on storefront
            $attributes['show_label'] = array_key_exists('show_label', $attributes) ? (bool) $attributes['show_label'] : true;

            $this->values()->updateOrCreate(
                [
                    'id' => array_get($attributes, 'id'),
                ],
                $attributes,
            );
        }
    }


    /**
     * Get the values for the product_banner.
     *
     * @return HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductBannerValue::class);
    }


    /**
     * Get the design file for the product_banner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function designFile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Entities\File::class, 'design_file_id');
    }


    /**
     * Scope a query to only include global product_banners.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeGlobals(Builder $query): Builder
    {
        return $query->where('is_global', true);
    }


    /**
     * Get table data for the resource
     *
     * @return ProductBannerTable
     */
    public function table(): ProductBannerTable
    {
        return new ProductBannerTable($this->newQuery()->globals());
    }


    protected function saveValuesForGlobal()
    {
        $this->saveValues(request('values', []));
    }


    protected function saveValuesForLocal()
    {
        $this->saveValues(
            request('product_banners.' . $this->uid . '.values', [])
        );
    }


    /**
     * @param $values
     *
     * @return Collection
     */
    private function getDeleteCandidates($values): Collection
    {
        return $this->values()
            ->pluck('id')
            ->diff(array_pluck($values, 'id'));
    }
}
