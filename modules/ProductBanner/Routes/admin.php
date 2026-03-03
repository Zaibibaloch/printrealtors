<?php

use Illuminate\Support\Facades\Route;

Route::get('product-banners', [
    'as' => 'admin.product_banners.index',
    'uses' => 'ProductBannerController@index',
    'middleware' => 'can:admin.product_banners.index',
]);

Route::get('product-banners/create', [
    'as' => 'admin.product_banners.create',
    'uses' => 'ProductBannerController@create',
    'middleware' => 'can:admin.product_banners.create',
]);

Route::post('product-banners', [
    'as' => 'admin.product_banners.store',
    'uses' => 'ProductBannerController@store',
    'middleware' => 'can:admin.product_banners.create',
]);

Route::get('product-banners/{id}', [
    'as' => 'admin.product_banners.show',
    'uses' => 'ProductBannerController@show',
    'middleware' => 'can:admin.product_banners.index',
]);

Route::get('product-banners/{id}/edit', [
    'as' => 'admin.product_banners.edit',
    'uses' => 'ProductBannerController@edit',
    'middleware' => 'can:admin.product_banners.edit',
]);

Route::put('product-banners/{id}', [
    'as' => 'admin.product_banners.update',
    'uses' => 'ProductBannerController@update',
    'middleware' => 'can:admin.product_banners.edit',
]);

Route::delete('product-banners/{ids}', [
    'as' => 'admin.product_banners.destroy',
    'uses' => 'ProductBannerController@destroy',
    'middleware' => 'can:admin.product_banners.destroy',
]);

Route::get('product-banners/index/table', [
    'as' => 'admin.product_banners.table',
    'uses' => 'ProductBannerController@table',
    'middleware' => 'can:admin.product_banners.index',
]);
