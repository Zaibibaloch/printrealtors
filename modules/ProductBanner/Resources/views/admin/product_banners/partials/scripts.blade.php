@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('admin::admin.shortcuts.back_to_index', ['name' => trans('product_banner::product_banners.product_banner')]) }}</dd>
    </dl>
@endpush

@push('globals')
    <script>
        FleetCart.langs['product_banner::product_banners.group.general'] = '{{ trans('product_banner::product_banners.group.general') }}';
        FleetCart.langs['product_banner::attributes.name'] = '{{ trans('product_banner::attributes.name') }}';
        FleetCart.langs['product_banner::attributes.type'] = '{{ trans('product_banner::attributes.type') }}';
        FleetCart.langs['product_banner::product_banners.form.product_banner_types.please_select'] = '{{ trans('product_banner::product_banners.form.product_banner_types.please_select') }}';
        FleetCart.langs['product_banner::product_banners.form.product_banner_types.text'] = '{{ trans('product_banner::product_banners.form.product_banner_types.text') }}';
        FleetCart.langs['product_banner::product_banners.form.product_banner_types.color'] = '{{ trans('product_banner::product_banners.form.product_banner_types.color') }}';
        FleetCart.langs['product_banner::product_banners.form.product_banner_types.image'] = '{{ trans('product_banner::product_banners.form.product_banner_types.image') }}';
        FleetCart.langs['product_banner::product_banners.group.values'] = '{{ trans('product_banner::product_banners.group.values') }}';
        FleetCart.langs['product_banner::product_banners.form.label'] = '{{ trans('product_banner::product_banners.form.label') }}';
        FleetCart.langs['product_banner::product_banners.form.color'] = '{{ trans('product_banner::product_banners.form.color') }}';
        FleetCart.langs['product_banner::product_banners.form.image'] = '{{ trans('product_banner::product_banners.form.image') }}';
        FleetCart.langs['product_banner::product_banners.form.add_row'] = '{{ trans('product_banner::product_banners.form.add_row') }}';
        FleetCart.langs['admin::admin.buttons.save'] = '{{ trans('admin::admin.buttons.save') }}';
    </script>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([{
            key: 'b',
            route: "{{ route('admin.product_banners.index') }}"
        }, ]);
    </script>
@endpush
