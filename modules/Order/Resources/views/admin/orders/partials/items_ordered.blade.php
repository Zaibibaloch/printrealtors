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
                                            <div class="customer-design-file"
                                                style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f1f1;">
                                                <span
                                                    style="font-size: 14px; font-weight: 600; color: #444444; display: block; margin-bottom: 8px;">
                                                    {{ trans('order::orders.customer_design') }}:
                                                </span>
                                                <a href="{{ $product->customerDesignFile->path }}" target="_blank"
                                                    download="{{ $product->customerDesignFile->filename }}"
                                                    onmouseover="this.style.backgroundColor='#0056b3'"
                                                    onmouseout="this.style.backgroundColor='#0068e1'"
                                                    style="display: inline-block; padding: 6px 12px; background-color: #0068e1; color: #ffffff; text-decoration: none; border-radius: 3px; font-size: 13px; font-weight: 400; transition: background-color 150ms ease-in-out; margin-bottom: 10px;">
                                                    <i class="fa fa-download" style="margin-right: 5px;"></i>
                                                    {{ trans('order::orders.download_design') }}
                                                </a>
                                                @if ($product->customerDesignFile->isImage())
                                                    <div style="margin-top: 10px;">
                                                        <img src="{{ $product->customerDesignFile->path }}"
                                                            alt="Customer Design"
                                                            style="max-width: 250px; max-height: 200px; border: 1px solid #e9e9e9; border-radius: 4px; display: block; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                    </div>
                                                @else
                                                    <div style="margin-top: 8px;">
                                                        <span
                                                            style="font-size: 13px; color: #9a9a9a; display: inline-block;">
                                                            <i class="fa fa-file" style="margin-right: 5px;"></i>
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
