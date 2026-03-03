@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('product_banner::product_banners.product_banner')]))
    @slot('subtitle', $product_banner->name)

    <li><a href="{{ route('admin.product_banners.index') }}">{{ trans('product_banner::product_banners.product_banners') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('product_banner::product_banners.product_banner')]) }}</li>
@endcomponent

@section('content')
    <div id="app" class="box"></div>
@endsection

@include('product_banner::admin.product_banners.partials.scripts')

@push('globals')
    <script type="module">
        FleetCart.data['product_banner'] = {!! $product_banner_resource !!};
    </script>

    @vite([
        'modules/ProductBanner/Resources/assets/admin/sass/main.scss',
        'modules/ProductBanner/Resources/assets/admin/js/edit.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
