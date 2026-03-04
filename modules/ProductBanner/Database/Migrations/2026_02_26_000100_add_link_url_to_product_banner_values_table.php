<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_banner_values', function (Blueprint $table) {
            if (!Schema::hasColumn('product_banner_values', 'link_url')) {
                $table->string('link_url')->nullable()->after('value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_banner_values', function (Blueprint $table) {
            if (Schema::hasColumn('product_banner_values', 'link_url')) {
                $table->dropColumn('link_url');
            }
        });
    }
};
