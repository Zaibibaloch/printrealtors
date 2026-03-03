@foreach ($product->productBanners as $productBanner)
    @if ($productBanner->values->isEmpty())
        @continue
    @endif

    <div class="product-banner-block">
        <div class="product-banner-title">
            {{ $productBanner->name }}:
        </div>

        @if (in_array($productBanner->type, ['image', 'design'], true))
            <div class="product-banner-media-grid">
                @foreach ($productBanner->values as $value)
                    @php
                        $media = $productBanner->type === 'design' ? ($value->design ?? null) : ($value->image ?? null);
                    @endphp

                    @if (!$media || empty($media->path))
                        @continue
                    @endif

                    <figure class="product-banner-media-item">
                        <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy">

                        @if (!empty($value->label))
                            <figcaption>{{ $value->label }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @elseif ($productBanner->type === 'color')
            <ul class="list-inline form-custom-radio custom-selection product-banner-color-list">
                @foreach ($productBanner->values as $value)
                    <li class="variation-color">
                        <div style="background-color: {{ $value->color }};"></div>
                    </li>
                @endforeach
            </ul>
        @else
            <ul class="list-inline form-custom-radio custom-selection">
                @foreach ($productBanner->values as $value)
                    <li>
                        {{ $value->label }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach
