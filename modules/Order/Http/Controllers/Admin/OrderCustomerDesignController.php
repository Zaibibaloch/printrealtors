<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Order\Entities\OrderProduct;
use Modules\Variation\Entities\Variation;

class OrderCustomerDesignController extends Controller
{
    /**
     * Delete the customer design file attached to a specific order product.
     *
     * @param int $orderId
     * @param int $orderProductId
     * @return RedirectResponse
     */
    public function destroy(int $orderId, int $orderProductId): RedirectResponse
    {
        /** @var OrderProduct $orderProduct */
        $orderProduct = OrderProduct::where('order_id', $orderId)
            ->where('id', $orderProductId)
            ->firstOrFail();

        $file = $orderProduct->customerDesignFile;

        // Detach file from this order product first.
        $orderProduct->update([
            'customer_design_file_id' => null,
        ]);

        if ($file) {
            $fileId = $file->id;

            // Check if the file is referenced anywhere else before deleting
            $otherOrderUsage = OrderProduct::where('customer_design_file_id', $fileId)->count();
            $variationUsage = Variation::where('design_file_id', $fileId)->count();

            if ($otherOrderUsage === 0 && $variationUsage === 0) {
                // Delete physical file if it exists
                $disk = $file->disk ?: config('filesystems.default');

                if ($file->path && Storage::disk($disk)->exists($file->path)) {
                    Storage::disk($disk)->delete($file->path);
                }

                $file->delete();
            }
        }

        return redirect()
            ->back()
            ->withSuccess(trans('order::orders.customer_design_deleted'));
    }
}

