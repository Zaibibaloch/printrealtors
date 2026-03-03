<?php

namespace Modules\ProductBanner\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Modules\Admin\Traits\HasCrudActions;
use Modules\ProductBanner\Entities\ProductBanner;
use Illuminate\Contracts\Foundation\Application;
use Modules\ProductBanner\Transformers\ProductBannerResource;
use Modules\ProductBanner\Http\Requests\SaveProductBannerRequest;

class ProductBannerController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected string $model = ProductBanner::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected string $label = 'product_banner::product_banners.product_banner';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected string $viewPath = 'product_banner::admin.product_banners';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected string|array $validation = SaveProductBannerRequest::class;


    public function show($id): ProductBannerResource
    {
        $entity = $this->getEntity($id);

        return new ProductBannerResource($entity);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Application|Factory|View
     */
    public function edit($id): View|Factory|Application
    {
        $entity = $this->getEntity($id);
        $product_bannerResource = new ProductBannerResource($entity);

        return view("{$this->viewPath}.edit",
            [
                'product_banner' => $entity,
                'product_banner_resource' => $product_bannerResource->response()->content(),
            ]
        );
    }
}
