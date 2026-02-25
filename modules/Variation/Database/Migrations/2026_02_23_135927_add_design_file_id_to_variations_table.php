<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add column if it does not exist
        Schema::table('variations', function (Blueprint $table) {
            if (!Schema::hasColumn('variations', 'design_file_id')) {
                // Match the type of files.id (INT UNSIGNED in this project)
                $table->unsignedInteger('design_file_id')->nullable()->after('position');
            }
        });

        // Add foreign key separately, and only if it doesn't already exist
        if (Schema::hasColumn('variations', 'design_file_id')) {
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'variations'
                      AND COLUMN_NAME = 'design_file_id'
                      AND CONSTRAINT_NAME != 'PRIMARY'
                ");

                if (empty($foreignKeys)) {
                    Schema::table('variations', function (Blueprint $table) {
                        $table->foreign('design_file_id')
                            ->references('id')
                            ->on('files')
                            ->onDelete('set null');
                    });
                }
            } catch (\Exception $e) {
                // If anything goes wrong (e.g., DB permissions / existing FK),
                // swallow the exception so the migration doesn't hard-fail.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variations', function (Blueprint $table) {
            if (Schema::hasColumn('variations', 'design_file_id')) {
                // Drop FK if exists
                try {
                    $table->dropForeign(['design_file_id']);
                } catch (\Exception $e) {
                    // ignore if FK name or existence is different
                }

                $table->dropColumn('design_file_id');
            }
        });
    }
};
