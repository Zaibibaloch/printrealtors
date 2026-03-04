<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_banners', function (Blueprint $table) {
            if (!Schema::hasColumn('product_banners', 'hide_value_labels')) {
                $table->boolean('hide_value_labels')
                    ->default(false)
                    ->after('hide_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_banners', function (Blueprint $table) {
            if (Schema::hasColumn('product_banners', 'hide_value_labels')) {
                $table->dropColumn('hide_value_labels');
            }
        });
    }
};

