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
        // First, check the data type of files.id column
        $filesIdType = DB::select("
            SELECT DATA_TYPE, COLUMN_TYPE 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'files' 
            AND COLUMN_NAME = 'id'
        ");
        
        $filesIdColumnType = $filesIdType[0]->COLUMN_TYPE ?? 'int(10) unsigned';
        $isBigInt = strpos(strtolower($filesIdColumnType), 'bigint') !== false;
        
        // Check if column already exists
        $columnExists = Schema::hasColumn('order_products', 'customer_design_file_id');
        
        if (!$columnExists) {
            // Use BIGINT UNSIGNED if files.id is BIGINT, otherwise INT UNSIGNED
            if ($isBigInt) {
                DB::statement('ALTER TABLE `order_products` ADD COLUMN `customer_design_file_id` BIGINT UNSIGNED NULL AFTER `line_total`');
            } else {
                DB::statement('ALTER TABLE `order_products` ADD COLUMN `customer_design_file_id` INT UNSIGNED NULL AFTER `line_total`');
            }
        } else {
            // Check current column type and modify if needed
            $currentColumnType = DB::select("
                SELECT COLUMN_TYPE 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'order_products' 
                AND COLUMN_NAME = 'customer_design_file_id'
            ");
            
            if (!empty($currentColumnType)) {
                $currentType = strtolower($currentColumnType[0]->COLUMN_TYPE);
                $needsModify = false;
                
                if ($isBigInt && strpos($currentType, 'bigint') === false) {
                    // Need to change to BIGINT
                    DB::statement('ALTER TABLE `order_products` MODIFY COLUMN `customer_design_file_id` BIGINT UNSIGNED NULL');
                    $needsModify = true;
                } elseif (!$isBigInt && strpos($currentType, 'bigint') !== false) {
                    // Need to change to INT
                    DB::statement('ALTER TABLE `order_products` MODIFY COLUMN `customer_design_file_id` INT UNSIGNED NULL');
                    $needsModify = true;
                }
            }
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
            'files_id_type' => $filesIdColumnType,
            'is_bigint' => $isBigInt,
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
