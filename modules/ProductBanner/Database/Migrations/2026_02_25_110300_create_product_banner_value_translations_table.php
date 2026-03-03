<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('product_banner_value_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_banner_value_id')->unsigned();
            $table->string('locale');
            $table->string('label');

            $table->unique(
                ['product_banner_value_id', 'locale'],
                'pbvt_pbv_id_locale_unique'
            );
            $table->foreign(
                'product_banner_value_id',
                'pbvt_pbv_id_foreign'
            )->references('id')->on('product_banner_values')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('product_banner_value_translations');
    }
};
