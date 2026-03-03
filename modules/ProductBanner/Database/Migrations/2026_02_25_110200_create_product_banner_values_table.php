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
    public function up()
    {
        Schema::create('product_banner_values', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid')->unique();
            $table->integer('product_banner_id')->unsigned()->index();
            $table->string('value')->nullable();
            $table->integer('position')->unsigned()->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('product_banner_values');
    }
};
