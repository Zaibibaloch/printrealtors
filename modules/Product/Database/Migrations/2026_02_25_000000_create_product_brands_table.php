<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('product_brands')) {
            Schema::create('product_brands', function (Blueprint $table) {
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('brand_id');

                $table->primary(['product_id', 'brand_id']);

                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade');

                $table->foreign('brand_id')
                    ->references('id')
                    ->on('brands')
                    ->onDelete('cascade');
            });
        }

        // Backfill existing product-brand relations so existing brand_id values
        // also appear in the pivot table for multi-brand support.
        if (Schema::hasTable('products') && Schema::hasTable('brands')) {
            $products = DB::table('products')
                ->whereNotNull('brand_id')
                ->pluck('brand_id', 'id');

            $rows = [];

            foreach ($products as $productId => $brandId) {
                $rows[] = [
                    'product_id' => (int) $productId,
                    'brand_id' => (int) $brandId,
                ];
            }

            if (!empty($rows)) {
                // Use insertOrIgnore to avoid duplicate key issues
                DB::table('product_brands')->insertOrIgnore($rows);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_brands');
    }
};
