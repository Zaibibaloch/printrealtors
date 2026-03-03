<?php

namespace Modules\ProductBanner\Entities;

use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Modules\Support\Eloquent\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ProductBannerValue extends Model
{
    use Translatable, HasMedia;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uid', 'value', 'position'];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected array $translatedAttributes = ['label'];

    /**
     * @var string[]
     */
    protected $appends = ['color', 'image', 'design'];


    /**
     * @return mixed|null
     */
    public function getColorAttribute(): mixed
    {
        return $this->value ?? null;
    }


    /**
     * @return mixed|null
     */
    public function getImageAttribute(): mixed
    {
        if ($this->relationLoaded('product_banner') && $this->product_banner && $this->product_banner->type === 'image') {
            return $this->files->first() ?? null;
        }
        
        // If product_banner is not loaded, check if this is an image type product_banner value
        if (!$this->relationLoaded('product_banner')) {
            return $this->files->first() ?? null;
        }
        
        return null;
    }


    /**
     * @return mixed|null
     */
    public function getDesignAttribute(): mixed
    {
        if ($this->relationLoaded('product_banner') && $this->product_banner && $this->product_banner->type === 'design') {
            return $this->files->first() ?? null;
        }
        
        // If product_banner is not loaded, check if this is a design type product_banner value
        if (!$this->relationLoaded('product_banner')) {
            return $this->files->first() ?? null;
        }
        
        return null;
    }


    /**
     * @return BelongsTo
     */
    public function product_banner(): BelongsTo
    {
        return $this->belongsTo(ProductBanner::class);
    }


    protected function extractMediaFromRequest()
    {
        if (request()->routeIs('admin.product_banners.*')) {
            return $this->extractMediaForGlobal();
        }

        if (request()->routeIs('admin.products.*')) {
            return $this->extractMediaForLocal();
        }
    }


    protected function extractMediaForGlobal()
    {
        $type = request('type');
        
        if ($type === 'image') {
            return [
                'media' => [request('values.' . $this->uid . '.image')],
            ];
        }
        
        if ($type === 'design') {
            return [
                'media' => [request('values.' . $this->uid . '.design')],
            ];
        }
    }


    protected function extractMediaForLocal()
    {
        $type = $this->product_banner->type;
        
        if ($type === 'image') {
            return [
                'media' => [request('product_banners.' . $this->product_banner->uid . '.values.' . $this->uid . '.image')],
            ];
        }
        
        if ($type === 'design') {
            return [
                'media' => [request('product_banners.' . $this->product_banner->uid . '.values.' . $this->uid . '.design')],
            ];
        }
    }
}
