<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_product_banners', function (Blueprint $table) {
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('product_banner_id');

            $table->primary(['product_id', 'product_banner_id'], 'ppb_product_banner_pk');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_banner_id')->references('id')->on('product_banners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_product_banners');
    }
};
