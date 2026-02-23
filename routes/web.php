<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Route::get('install', 'InstallController@installation')->name('install.show');
Route::post('install', 'InstallController@install')->name('install.do');

Route::get('license', 'LicenseController@create')->name('license.create');
Route::post('license', 'LicenseController@store')->name('license.store');

// Temporary route to run customer design migration
// TODO: Remove this route after migration is complete
Route::get('run-customer-design-migration', function () {
    try {
        // Check if column already exists
        $columnExists = Schema::hasColumn('order_products', 'customer_design_file_id');
        
        if (!$columnExists) {
            // Add column
            DB::statement('ALTER TABLE `order_products` ADD COLUMN `customer_design_file_id` INT UNSIGNED NULL AFTER `line_total`');
        }
        
        // Check if foreign key exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'order_products' 
            AND COLUMN_NAME = 'customer_design_file_id'
            AND CONSTRAINT_NAME != 'PRIMARY'
        ");
        
        if (empty($foreignKeys)) {
            // Add foreign key
            DB::statement("
                ALTER TABLE `order_products`
                ADD CONSTRAINT `order_products_customer_design_file_id_foreign`
                FOREIGN KEY (`customer_design_file_id`) 
                REFERENCES `files` (`id`) 
                ON DELETE SET NULL
            ");
        }
        
        // Add to migrations table if not exists
        $migrationExists = DB::table('migrations')
            ->where('migration', '2026_02_24_000001_add_customer_design_file_id_to_order_products_table')
            ->exists();
            
        if (!$migrationExists) {
            $maxBatch = DB::table('migrations')->max('batch') ?? 0;
            DB::table('migrations')->insert([
                'migration' => '2026_02_24_000001_add_customer_design_file_id_to_order_products_table',
                'batch' => $maxBatch + 1,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Migration completed successfully!',
            'column_exists' => $columnExists ? 'already existed' : 'created',
            'foreign_key_exists' => !empty($foreignKeys) ? 'already existed' : 'created',
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage(),
            'error' => $e->getTraceAsString(),
        ], 500);
    }
})->name('run.customer.design.migration');
