<?php

namespace Modules\ProductBanner\Entities;

use Modules\Support\Eloquent\TranslationModel;

class ProductBannerTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
