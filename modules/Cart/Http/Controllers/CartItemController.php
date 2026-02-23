<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Cart\Facades\Cart;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Checkers\ValidCoupon;
use Modules\Coupon\Checkers\MaximumSpend;
use Modules\Coupon\Checkers\MinimumSpend;
use Modules\Coupon\Checkers\CouponExists;
use Modules\Coupon\Checkers\AlreadyApplied;
use Modules\Coupon\Checkers\ExcludedProducts;
use Modules\Coupon\Checkers\ApplicableProducts;
use Modules\Coupon\Checkers\ExcludedCategories;
use Modules\Coupon\Checkers\UsageLimitPerCoupon;
use Modules\Cart\Http\Middleware\CheckItemStock;
use Modules\Coupon\Checkers\ApplicableCategories;
use Modules\Coupon\Checkers\UsageLimitPerCustomer;
use Modules\Cart\Http\Requests\StoreCartItemRequest;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Storage;

class CartItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(CheckItemStock::class)
            ->only(['store', 'update']);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCartItemRequest $request
     *
     * @return \Modules\Cart\Cart
     */
    public function store(StoreCartItemRequest $request)
    {
        Cart::store(
            $request->product_id,
            $request->variant_id,
            $request->qty,
            $request->options ?? [],
            $request->customer_design_file_id ?? null,
        );

        return Cart::instance();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param string $id
     *
     * @return \Modules\Cart\Cart
     */
    public function update(string $id)
    {
        Cart::updateQuantity($id, request('qty'));

        return Cart::instance();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     *
     * @return \Modules\Cart\Cart
     */
    public function destroy(string $id)
    {
        Cart::remove($id);

        return Cart::instance();
    }


    /**
     * Upload customer design file.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCustomerDesign(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                function ($attribute, $value, $fail) {
                    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                    $allowedExtensions = ['ai', 'eps', 'psd', 'cdr'];
                    
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mime = $value->getClientMimeType();
                    
                    if (!in_array($mime, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
                        $fail('Invalid file type. Please upload image, PDF, AI, EPS, PSD, or CDR files.');
                    }
                },
            ],
        ]);

        $file = $request->file('file');
        $path = Storage::putFile('media/customer-designs', $file);

        $uploadedFile = File::create([
            'user_id' => auth()->id() ?? 0,
            'disk' => config('filesystems.default'),
            'filename' => substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'extension' => $file->guessClientExtension() ?? '',
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'file_id' => $uploadedFile->id,
            'filename' => $uploadedFile->filename,
            'path' => $uploadedFile->path,
        ]);
    }
}
