<?php

namespace Modules\ProductBanner\Entities;

use Modules\Support\Eloquent\TranslationModel;

class ProductBannerValueTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['label'];
}
