<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('variations', 'design_file_id')) {
            return;
        }

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'variations'
              AND COLUMN_NAME = 'design_file_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE `variations` DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
        }

        Schema::table('variations', function (Blueprint $table) {
            $table->dropColumn('design_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('variations', 'design_file_id')) {
            return;
        }

        Schema::table('variations', function (Blueprint $table) {
            $table->unsignedInteger('design_file_id')->nullable()->after('position');
            $table->foreign('design_file_id')->references('id')->on('files')->onDelete('set null');
        });
    }
};
