@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('product_banner::product_banners.product_banner')]))

    <li><a href="{{ route('admin.product_banners.index') }}">{{ trans('product_banner::product_banners.product_banners') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('product_banner::product_banners.product_banner')]) }}</li>
@endcomponent

@section('content')
    <div id="app" class="box"></div>
@endsection

@include('product_banner::admin.product_banners.partials.scripts')

@push('globals')
    @vite([
        'modules/ProductBanner/Resources/assets/admin/sass/main.scss',
        'modules/ProductBanner/Resources/assets/admin/js/create.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
