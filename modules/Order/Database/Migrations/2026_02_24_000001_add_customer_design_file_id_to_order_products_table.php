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
        Schema::table('order_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_products', 'customer_design_file_id')) {
                $table->unsignedInteger('customer_design_file_id')->nullable()->after('line_total');
            }
        });

        // Add foreign key if column exists but foreign key doesn't
        if (Schema::hasColumn('order_products', 'customer_design_file_id')) {
            try {
                Schema::table('order_products', function (Blueprint $table) {
                    $table->foreign('customer_design_file_id')->references('id')->on('files')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, ignore
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
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropForeign(['customer_design_file_id']);
            $table->dropColumn('customer_design_file_id');
        });
    }
};
