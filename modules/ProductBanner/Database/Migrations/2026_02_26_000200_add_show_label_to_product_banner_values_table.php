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
    public function up()
    {
        Schema::table('product_banner_values', function (Blueprint $table) {
            if (!Schema::hasColumn('product_banner_values', 'show_label')) {
                $table->boolean('show_label')
                    ->default(true)
                    ->after('link_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_banner_values', function (Blueprint $table) {
            if (Schema::hasColumn('product_banner_values', 'show_label')) {
                $table->dropColumn('show_label');
            }
        });
    }
};

