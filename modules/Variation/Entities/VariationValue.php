<?php

namespace Modules\Variation\Entities;

use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Modules\Support\Eloquent\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class VariationValue extends Model
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
        if ($this->relationLoaded('variation') && $this->variation && $this->variation->type === 'image') {
            return $this->files->first() ?? null;
        }
        
        // If variation is not loaded, check if this is an image type variation value
        if (!$this->relationLoaded('variation')) {
            return $this->files->first() ?? null;
        }
        
        return null;
    }


    /**
     * @return mixed|null
     */
    public function getDesignAttribute(): mixed
    {
        if ($this->relationLoaded('variation') && $this->variation && $this->variation->type === 'design') {
            return $this->files->first() ?? null;
        }
        
        // If variation is not loaded, check if this is a design type variation value
        if (!$this->relationLoaded('variation')) {
            return $this->files->first() ?? null;
        }
        
        return null;
    }


    /**
     * @return BelongsTo
     */
    public function variation(): BelongsTo
    {
        return $this->belongsTo(Variation::class);
    }


    protected function extractMediaFromRequest()
    {
        if (request()->routeIs('admin.variations.*')) {
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
        $type = $this->variation->type;
        
        if ($type === 'image') {
            return [
                'media' => [request('variations.' . $this->variation->uid . '.values.' . $this->uid . '.image')],
            ];
        }
        
        if ($type === 'design') {
            return [
                'media' => [request('variations.' . $this->variation->uid . '.values.' . $this->uid . '.design')],
            ];
        }
    }
}
