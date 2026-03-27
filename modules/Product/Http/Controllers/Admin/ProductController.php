<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Transformers\ProductEditResource;

class ProductController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected string $model = Product::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected string $label = 'product::products.product';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected string $viewPath = 'product::admin.products';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected string|array $validation = SaveProductRequest::class;


    /**
     * Store a newly created resource in storage.
     *
     * @return Response|JsonResponse
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->all()
        );

        $this->searchable($entity);

        $message = trans('admin::messages.resource_created', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess($message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Factory|View|Application
     */
    public function edit($id): Factory|View|Application
    {
        $entity = $this->getEntity($id);
        $productEditResource = new ProductEditResource($entity);

        return view(
            "{$this->viewPath}.edit",
            [
                'product' => $entity,
                'product_resource' => $productEditResource->response()->content(),
            ]
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     */
    public function update($id)
    {
        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $entity->update(
            $this->getRequest('update')->all()
        );

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $productEditResource = new ProductEditResource($entity);

        $this->searchable($entity);

        $message = trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_resource' => $productEditResource,
                ],
                200
            );
        }
    }


    /**
     * Duplicate a product and redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate($id)
    {
        $source = Product::withoutGlobalScope('active')->findOrFail($id);
        $duplicated = $this->createDuplicate($source);

        $this->searchable($duplicated);

        return redirect()
            ->route('admin.products.edit', $duplicated->id)
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => $this->getLabel()]));
    }


    public function duplicateMany(string $ids): JsonResponse
    {
        $sourceIds = collect(explode(',', $ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        if ($sourceIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No products selected for duplication.',
            ], 422);
        }

        $createdCount = 0;

        DB::transaction(function () use ($sourceIds, &$createdCount) {
            Product::withoutGlobalScope('active')
                ->whereIn('id', $sourceIds)
                ->get()
                ->each(function (Product $source) use (&$createdCount) {
                    $duplicate = $this->createDuplicate($source);
                    $this->searchable($duplicate);
                    $createdCount++;
                });
        });

        return response()->json([
            'success' => true,
            'message' => "{$createdCount} product(s) duplicated successfully.",
        ]);
    }


    private function createDuplicate(Product $source): Product
    {
        $payload = $this->duplicatePayload($source);

        $this->disableSearchSyncing();

        return DB::transaction(function () use ($payload) {
            $originalRequestData = request()->all();

            request()->replace($payload);

            try {
                return $this->getModel()->create($payload);
            } finally {
                request()->replace($originalRequestData);
            }
        });
    }


    private function duplicatePayload(Product $source): array
    {
        $data = json_decode((new ProductEditResource($source))->toJson(), true);

        $valueUidMap = [];

        $data['id'] = null;
        $data['name'] = "{$data['name']} (Copy)";
        $data['slug'] = null;
        $data['sku'] = null;
        $data['brand_id'] = empty($data['brand_id']) ? null : $data['brand_id'];
        $data['tax_class_id'] = empty($data['tax_class_id']) ? null : $data['tax_class_id'];
        $data['is_active'] = false;
        $data['manage_stock'] = (bool) ($data['manage_stock'] ?? false);
        $data['in_stock'] = (bool) ($data['in_stock'] ?? true);
        $data['is_virtual'] = (bool) ($data['is_virtual'] ?? false);

        $data['media'] = collect($data['media'] ?? [])->pluck('id')->filter()->values()->all();
        $data['downloads'] = collect($data['downloads'] ?? [])->pluck('id')->filter()->values()->all();
        $data['brands'] = collect($data['brands'] ?? [])->values()->all();
        $data['categories'] = collect($data['categories'] ?? [])->values()->all();
        $data['tags'] = collect($data['tags'] ?? [])->values()->all();
        $data['up_sells'] = collect($data['up_sells'] ?? [])->values()->all();
        $data['cross_sells'] = collect($data['cross_sells'] ?? [])->values()->all();
        $data['related_products'] = collect($data['related_products'] ?? [])->values()->all();
        $data['meta'] = $data['meta'] ?? ['meta_title' => null, 'meta_description' => null];

        $data['attributes'] = collect($data['attributes'] ?? [])
            ->map(function ($attribute) {
                $attribute['uid'] = Str::random(32);
                $attribute['id'] = null;

                return $attribute;
            })
            ->keyBy('uid')
            ->all();

        $data['variations'] = collect($data['variations'] ?? [])
            ->map(function ($variation) use (&$valueUidMap) {
                $variation['id'] = null;
                $variation['is_global'] = false;
                $variation['uid'] = Str::random(32);

                $variation['values'] = collect($variation['values'] ?? [])
                    ->map(function ($value) use (&$valueUidMap) {
                        $oldUid = $value['uid'] ?? Str::random(32);
                        $newUid = Str::random(32);

                        $valueUidMap[$oldUid] = $newUid;
                        $value['id'] = null;
                        $value['uid'] = $newUid;
                        $value['image'] = $value['image']['id'] ?? null;

                        return $value;
                    })
                    ->keyBy('uid')
                    ->all();

                return $variation;
            })
            ->keyBy('uid')
            ->all();

        $data['options'] = collect($data['options'] ?? [])
            ->map(function ($option) {
                $option['id'] = null;
                $option['uid'] = Str::random(32);
                $option['is_global'] = false;

                $option['values'] = collect($option['values'] ?? [])
                    ->map(function ($value) {
                        $value['id'] = null;
                        $value['uid'] = Str::random(32);

                        return $value;
                    })
                    ->keyBy('uid')
                    ->all();

                return $option;
            })
            ->keyBy('uid')
            ->all();

        $data['product_banners'] = collect($data['product_banners'] ?? [])
            ->map(function ($productBanner) {
                $productBanner['id'] = null;
                $productBanner['uid'] = Str::random(32);
                $productBanner['is_global'] = false;

                $productBanner['values'] = collect($productBanner['values'] ?? [])
                    ->map(function ($value) use ($productBanner) {
                        $value['id'] = null;
                        $value['uid'] = Str::random(32);
                        $value['image'] = $productBanner['type'] === 'image' ? ($value['image']['id'] ?? null) : null;
                        $value['design'] = $productBanner['type'] === 'design' ? ($value['design']['id'] ?? null) : null;

                        return $value;
                    })
                    ->keyBy('uid')
                    ->all();

                return $productBanner;
            })
            ->keyBy('uid')
            ->all();

        $data['variants'] = collect($data['variants'] ?? [])
            ->map(function ($variant) use ($valueUidMap) {
                $variant['id'] = null;
                $newUids = collect(explode('.', $variant['uids'] ?? ''))
                    ->filter()
                    ->map(fn ($uid) => $valueUidMap[$uid] ?? $uid)
                    ->implode('.');

                $variant['uids'] = $newUids;
                $variant['uid'] = md5($newUids ?: Str::random(16));
                $variant['media'] = collect($variant['media'] ?? [])->pluck('id')->filter()->values()->all();

                return $variant;
            })
            ->keyBy('uid')
            ->all();

        return $data;
    }
}
