@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::products.products'))

    <li class="active">{{ trans('product::products.products') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'products')
    @slot('name', trans('product::products.product'))

    @slot('thead')
        @include('product::admin.products.partials.thead', ['name' => 'products-index'])
    @endslot
@endcomponent

@if (session()->has('exit_flash'))
    @push('notifications')
        <div class="alert alert-success fade in alert-dismissible clearfix">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z" fill="#555555"/>
            </svg>
            
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M5.00082 14.9995L14.9999 5.00041" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.9999 14.9996L5.00082 5.00049" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <span class="alert-text">{{ session('exit_flash') }}</span>
        </div>
    @endpush
@endif

@push('scripts')
    <script type="module">
        const selector = '#products-table .table';
        const table = new DataTable(selector, {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'thumbnail', orderable: false, searchable: false, width: '10%' },
                { data: 'name', name: 'translations.name', class: 'name', orderable: false, defaultContent: '' },
                { data: 'price', searchable: false },
                { data: 'in_stock', name: 'in_stock', searchable: false },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'updated', name: 'updated_at' },
            ]
        }, function () {
            const duplicateButton = $(`
                <button type="button" class="btn btn-default btn-duplicate m-l-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16" fill="none">
                        <path d="M9.33366 1.33325H3.33366C2.59728 1.33325 2.00033 1.93021 2.00033 2.66659V10.6666H3.33366V2.66659H9.33366V1.33325Z" stroke="#141B34" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.33366 5.33325H11.3337C12.0701 5.33325 12.667 5.93021 12.667 6.66659V13.3333C12.667 14.0696 12.0701 14.6666 11.3337 14.6666H5.33366C4.59728 14.6666 4.00033 14.0696 4.00033 13.3333V6.66659C4.00033 5.93021 4.59728 5.33325 5.33366 5.33325Z" stroke="#020010" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>{{ trans('admin::admin.buttons.duplicate') }}</span>
                </button>
            `).appendTo(
                $(selector).closest(".dt-container").find(".dt-length")
            );

            duplicateButton.on('click', async () => {
                const selectedIds = DataTable.getSelectedIds(selector);

                if (!selectedIds.length) {
                    return;
                }

                if (!confirm('{{ trans('admin::admin.duplicate.confirmation_message') }}')) {
                    return;
                }

                try {
                    await axios.post(`/admin/products/${selectedIds.join(',')}/duplicate`);
                    DataTable.setSelectedIds(selector, []);
                    DataTable.reload($(selector), null, false);
                    toaster('Selected products duplicated successfully.', { type: 'success' });
                } catch (e) {
                    const message = e?.response?.data?.message || 'Failed to duplicate selected products.';
                    toaster(message, { type: 'error' });
                }
            });
        });
    </script>
@endpush
