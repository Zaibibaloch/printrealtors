@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product_banner::product_banners.product_banners'))

    <li class="active">{{ trans('product_banner::product_banners.product_banners') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'product_banners')
    @slot('name', trans('product_banner::product_banners.product_banner'))

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')

            <th>{{ trans('admin::admin.table.id') }}</th>
            <th>{{ trans('product_banner::product_banners.table.name') }}</th>
            <th>{{ trans('product_banner::product_banners.table.type') }}</th>
            <th data-sort>{{ trans('admin::admin.table.updated') }}</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#product_banners-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'translations.name', orderable: false, defaultContent: '' },
                { data: 'type', name: 'type' },
                { data: 'updated', name: 'updated_at' },
            ],
        });
    </script>
@endpush
