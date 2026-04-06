@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('admin::admin.shortcuts.back_to_index', ['name' => trans('product::products.product')]) }}</dd>
        @if (isset($product) && $product?->id)
            <dt><code>d</code></dt>
            <dd>Duplicate this product</dd>
        @endif
    </dl>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'b', route: "{{ route('admin.products.index') }}" },
            @if (isset($product) && $product?->id)
                { key: 'd', route: "{{ route('admin.products.duplicate', $product->id) }}" },
            @endif
        ]);
    </script>
@endpush
