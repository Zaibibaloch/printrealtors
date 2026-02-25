<div class="items-ordered-wrapper">
    <h4 class="section-title">{{ trans('order::orders.items_ordered') }}</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="items-ordered">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ trans('order::orders.product') }}</th>
                                <th>{{ trans('order::orders.unit_price') }}</th>
                                <th>{{ trans('order::orders.quantity') }}</th>
                                <th>{{ trans('order::orders.line_total') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($order->products as $product)
                                <tr>
                                    <td>
                                        @if ($product->trashed())
                                            {{ $product->name }}
                                        @else
                                            <a
                                                href="{{ route('admin.products.edit', $product->product->id) }}">{{ $product->name }}</a>
                                        @endif

                                        @if ($product->hasAnyVariation())
                                            <br>
                                            @foreach ($product->variations as $variation)
                                                <span>
                                                    {{ $variation->name }}:

                                                    <span>
                                                        {{ $variation->values()->first()?->label }}{{ $loop->last ? '' : ',' }}
                                                    </span>
                                                </span>
                                            @endforeach
                                        @endif

                                        @if ($product->hasAnyOption())
                                            <br>
                                            @foreach ($product->options as $option)
                                                <span>
                                                    {{ $option->name }}:

                                                    <span>
                                                        @if ($option->option->isFieldType())
                                                            {{ $option->value }}
                                                        @else
                                                            {{ $option->values->implode('label', ', ') }}
                                                        @endif
                                                    </span>
                                                </span>
                                            @endforeach
                                        @endif

                                        @if ($product->customerDesignFile)
                                            <br>
                                            <div class="customer-design-file">
                                                <span>
                                                    {{ trans('order::orders.customer_design') }}:
                                                </span>

                                                <a href="{{ $product->customerDesignFile->path }}" target="_blank"
                                                    download="{{ $product->customerDesignFile->filename }}">
                                                    <i class="fa fa-download"></i>
                                                    {{ trans('order::orders.download_design') }}
                                                </a>

                                                <button type="button"
                                                    class="btn btn-sm btn-danger delete-customer-design"
                                                    data-action="{{ route('admin.orders.products.customer_design.destroy', [$order->id, $product->id]) }}"
                                                    data-confirm>
                                                    <i class="fa fa-trash"></i>
                                                    {{ trans('order::orders.delete_design') }}
                                                </button>

                                                @if ($product->customerDesignFile->isImage())
                                                    <div>
                                                        <img src="{{ $product->customerDesignFile->path }}"
                                                            alt="Customer Design">
                                                    </div>
                                                @else
                                                    <div>
                                                        <span>
                                                            <i class="fa fa-file"></i>
                                                            {{ $product->customerDesignFile->filename }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $product->unit_price->format() }}
                                    </td>

                                    <td>{{ $product->qty }}</td>

                                    <td>
                                        {{ $product->line_total->format() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
