<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('product_banner_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_banner_id')->unsigned();
            $table->string('locale');
            $table->string('name');

            $table->unique(['product_banner_id', 'locale']);
            $table->foreign('product_banner_id')->references('id')->on('product_banners')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_banner_translations');
    }
};
