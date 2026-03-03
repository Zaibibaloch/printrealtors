<?php

namespace Modules\ProductBanner\Admin;

use Modules\Admin\Ui\AdminTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Exceptions\Exception;

class ProductBannerTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return EloquentDataTable
     * @throws Exception
     */
    public function make(): EloquentDataTable
    {
        return $this->newTable()
            ->editColumn('type', function ($product_banner) {
                $translationKey = "product_banner::product_banners.form.product_banner_types.{$product_banner->type}";
                $translated = trans($translationKey);
                
                // If translation returns the key itself, return a fallback
                if ($translated === $translationKey) {
                    return ucfirst($product_banner->type);
                }
                
                return $translated;
            })
            ->removeColumn('values');
    }
}
