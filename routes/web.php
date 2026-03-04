<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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


// Temporary route to run product brands & variation design_file_id migrations on live,
// where php artisan migrate cannot be executed due to PHP version constraints.
// TODO: Remove this route after running it once on live.
Route::get('run-brand-and-variation-migrations', function () {
    try {
        $details = [];

        // ---------------------------------------------------------------------
        // 1) Ensure product_brands pivot table exists and is backfilled
        // ---------------------------------------------------------------------

        // Detect ID types for products and brands so we can match the FK column types
        $productsIdInfo = DB::select("SHOW COLUMNS FROM `products` LIKE 'id'");
        $brandsIdInfo = DB::select("SHOW COLUMNS FROM `brands` LIKE 'id'");

        $productsIdType = $productsIdInfo[0]->Type ?? 'int(10) unsigned';
        $brandsIdType = $brandsIdInfo[0]->Type ?? 'int(10) unsigned';

        $productsIsBigInt = str_contains(strtolower($productsIdType), 'bigint');
        $brandsIsBigInt = str_contains(strtolower($brandsIdType), 'bigint');

        $productsColumnTypeSql = $productsIsBigInt ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';
        $brandsColumnTypeSql = $brandsIsBigInt ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';

        $details['products_id_type'] = $productsIdType;
        $details['brands_id_type'] = $brandsIdType;

        if (!Schema::hasTable('product_brands')) {
            DB::statement("
                CREATE TABLE `product_brands` (
                    `product_id` {$productsColumnTypeSql} NOT NULL,
                    `brand_id` {$brandsColumnTypeSql} NOT NULL,
                    PRIMARY KEY (`product_id`, `brand_id`),
                    CONSTRAINT `product_brands_product_id_foreign`
                        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
                    CONSTRAINT `product_brands_brand_id_foreign`
                        FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $details['product_brands_table'] = 'created';
        } else {
            $details['product_brands_table'] = 'already existed';
        }

        // Backfill existing product-brand relations from products.brand_id
        DB::statement("
            INSERT IGNORE INTO `product_brands` (`product_id`, `brand_id`)
            SELECT `id`, `brand_id`
            FROM `products`
            WHERE `brand_id` IS NOT NULL
        ");

        $details['product_brands_backfill'] = 'done';

        // ---------------------------------------------------------------------
        // 2) Ensure variations.design_file_id column and foreign key
        // ---------------------------------------------------------------------

        // Detect files.id type so we can match it
        $filesIdInfo = DB::select("SHOW COLUMNS FROM `files` LIKE 'id'");
        $filesIdType = $filesIdInfo[0]->Type ?? 'int(10) unsigned';
        $filesIsBigInt = str_contains(strtolower($filesIdType), 'bigint');
        $filesColumnTypeSql = $filesIsBigInt ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';

        $details['files_id_type'] = $filesIdType;

        // Add or fix column
        $designColumnExists = Schema::hasColumn('variations', 'design_file_id');

        if (!$designColumnExists) {
            DB::statement("
                ALTER TABLE `variations`
                ADD COLUMN `design_file_id` {$filesColumnTypeSql} NULL AFTER `position`
            ");

            $details['variations_design_file_id_column'] = 'created';
        } else {
            // Ensure column type matches files.id type
            $currentDesignColumnInfo = DB::select("
                SHOW COLUMNS FROM `variations` LIKE 'design_file_id'
            ");

            if (!empty($currentDesignColumnInfo)) {
                $currentType = strtolower($currentDesignColumnInfo[0]->Type);
                $targetIsBigInt = $filesIsBigInt;

                if ($targetIsBigInt && !str_contains($currentType, 'bigint')) {
                    DB::statement("
                        ALTER TABLE `variations`
                        MODIFY COLUMN `design_file_id` {$filesColumnTypeSql} NULL AFTER `position`
                    ");
                    $details['variations_design_file_id_column'] = 'modified to BIGINT';
                } elseif (!$targetIsBigInt && str_contains($currentType, 'bigint')) {
                    DB::statement("
                        ALTER TABLE `variations`
                        MODIFY COLUMN `design_file_id` {$filesColumnTypeSql} NULL AFTER `position`
                    ");
                    $details['variations_design_file_id_column'] = 'modified to INT';
                } else {
                    $details['variations_design_file_id_column'] = 'already correct';
                }
            }
        }

        // Add foreign key if not exists
        $variationFks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'variations'
              AND COLUMN_NAME = 'design_file_id'
              AND CONSTRAINT_NAME != 'PRIMARY'
        ");

        if (empty($variationFks)) {
            DB::statement("
                ALTER TABLE `variations`
                ADD CONSTRAINT `variations_design_file_id_foreign`
                FOREIGN KEY (`design_file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL
            ");

            $details['variations_design_file_id_foreign'] = 'created';
        } else {
            $details['variations_design_file_id_foreign'] = 'already existed';
        }

        // ---------------------------------------------------------------------
        // 3) Mark corresponding migrations as run in the migrations table
        // ---------------------------------------------------------------------

        if (Schema::hasTable('migrations')) {
            $migrationNames = [
                '2026_02_23_135927_add_design_file_id_to_variations_table',
                '2026_02_25_000000_create_product_brands_table',
            ];

            $maxBatch = DB::table('migrations')->max('batch') ?? 0;
            $nextBatch = $maxBatch + 1;

            foreach ($migrationNames as $migrationName) {
                $exists = DB::table('migrations')
                    ->where('migration', $migrationName)
                    ->exists();

                if (!$exists) {
                    DB::table('migrations')->insert([
                        'migration' => $migrationName,
                        'batch' => $nextBatch,
                    ]);

                    $details['migration_marked_' . $migrationName] = 'inserted';
                    $nextBatch++;
                } else {
                    $details['migration_marked_' . $migrationName] = 'already exists';
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand pivot & variation design_file_id migrations completed successfully!',
            'details' => $details,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage(),
            'error' => $e->getTraceAsString(),
        ], 500);
    }
})->name('run.brand_and_variation.migrations');


// Temporary route to backfill product_brands pivot table from products.brand_id
// so that existing product-brand relations appear in the new multi-brand UI.
// TODO: Remove this route after running it once on live.
Route::get('backfill-product-brands', function () {
    try {
        $details = [];

        // Ensure pivot table exists (simple version, INT UNSIGNED columns).
        if (!Schema::hasTable('product_brands')) {
            DB::statement("
                CREATE TABLE `product_brands` (
                    `product_id` INT UNSIGNED NOT NULL,
                    `brand_id` INT UNSIGNED NOT NULL,
                    PRIMARY KEY (`product_id`, `brand_id`),
                    CONSTRAINT `product_brands_product_id_foreign`
                        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
                    CONSTRAINT `product_brands_brand_id_foreign`
                        FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $details['product_brands_table'] = 'created';
        } else {
            $details['product_brands_table'] = 'already existed';
        }

        // Copy all existing product.brand_id values into product_brands.
        DB::statement("
            INSERT IGNORE INTO `product_brands` (`product_id`, `brand_id`)
            SELECT `id`, `brand_id`
            FROM `products`
            WHERE `brand_id` IS NOT NULL
        ");

        $details['backfill'] = 'completed from products.brand_id';

        return response()->json([
            'success' => true,
            'message' => 'product_brands backfilled from products.brand_id successfully.',
            'details' => $details,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Backfill failed: ' . $e->getMessage(),
            'error' => $e->getTraceAsString(),
        ], 500);
    }
})->name('backfill.product_brands');


// Temporary route to clear and optimize application caches on live server
// where running php artisan commands over SSH is not convenient.
// TODO: Remove this route after using it when needed.
Route::get('clear-all-caches', function () {
    try {
        $results = [];

        $results['config_clear'] = Artisan::call('config:clear');
        $results['cache_clear'] = Artisan::call('cache:clear');
        $results['view_clear'] = Artisan::call('view:clear');
        $results['route_clear'] = Artisan::call('route:clear');
        $results['optimize_clear'] = Artisan::call('optimize:clear');

        return response()->json([
            'success' => true,
            'message' => 'All caches cleared and optimization reset successfully.',
            'results' => $results,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to clear caches: ' . $e->getMessage(),
        ], 500);
    }
})->name('clear.all.caches');


// Temporary route to run recent Product Banner / Variation cleanup migrations on live,
// where SSH access for artisan commands is not available.
// TODO: Remove this route after running it once on production.
Route::get('run-product-banner-migrations', function () {
    try {
        $details = [];

        $migrationPaths = [
            // ProductBanner module migrations (new module tables + design support).
            'modules/ProductBanner/Database/Migrations/2026_02_25_110000_create_product_banners_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_25_110100_create_product_banner_translations_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_25_110200_create_product_banner_values_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_25_110300_create_product_banner_value_translations_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_25_110400_add_design_file_id_to_product_banners_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_26_000000_add_display_controls_to_product_banners_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_26_000100_add_link_url_to_product_banner_values_table.php',
            'modules/ProductBanner/Database/Migrations/2026_02_26_000200_add_show_label_to_product_banner_values_table.php',
            'modules/ProductBanner/Database/Migrations/2026_03_03_170000_ensure_hide_value_labels_column_on_product_banners_table.php',
            // Creates product_product_banners pivot table.
            'modules/Product/Database/Migrations/2026_02_25_130000_create_product_product_banners_table.php',
            // Drops variations.design_file_id safely if it exists.
            'modules/Variation/Database/Migrations/2026_02_25_120000_remove_design_file_id_from_variations_table.php',
        ];

        foreach ($migrationPaths as $path) {
            Artisan::call('migrate', [
                '--path' => $path,
                '--force' => true,
            ]);

            $details[$path] = trim(Artisan::output()) ?: 'executed';
        }

        return response()->json([
            'success' => true,
            'message' => 'Product banner related migrations executed successfully.',
            'details' => $details,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration execution failed: ' . $e->getMessage(),
            'error' => $e->getTraceAsString(),
        ], 500);
    }
})->name('run.product_banner.migrations');
