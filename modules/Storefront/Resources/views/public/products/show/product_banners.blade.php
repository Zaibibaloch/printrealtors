@foreach ($productBanners as $productBanner)
    @if ($productBanner->values->isEmpty())
        @continue
    @endif

    <div class="product-banner-block">
        @unless ($productBanner->hide_title)
            <div class="product-banner-title">
                {{ $productBanner->name }}:
            </div>
        @endunless

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
                        @if (!empty($value->link_url))
                            <a href="{{ $value->link_url }}" target="_blank" rel="noopener noreferrer nofollow" style="display:block;">
                                <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy" style="cursor:pointer;">
                            </a>
                        @else
                            <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy">
                        @endif

                        {{-- Admin: checked = hide this label on storefront; unchecked = show this label --}}
                        @if (!empty($value->label) && !$value->show_label && !$productBanner->hide_value_labels)
                            <figcaption>{{ $value->label }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>

        @elseif ($productBanner->type === 'color')
            <ul class="list-inline form-custom-radio custom-selection product-banner-color-list">
                @foreach ($productBanner->values as $value)
                    @if (empty($value->color))
                        @continue
                    @endif
                    <li class="variation-color">
                        @if (!empty($value->link_url))
                            <a href="{{ $value->link_url }}" target="_blank" rel="noopener noreferrer nofollow" title="{{ $value->label }}" style="cursor:pointer;">
                                <div style="background-color: {{ $value->color }}; min-width: 40px; min-height: 40px;"></div>
                            </a>
                        @else
                            <div style="background-color: {{ $value->color }}; min-width: 40px; min-height: 40px;"></div>
                        @endif
                    </li>
                @endforeach
            </ul>
            
            {{-- Show color labels (no hex code in text) --}}
            @php
                $colorTextItems = [];
                foreach ($productBanner->values as $value) {
                    if (empty($value->label) || empty($value->color)) continue;
                    
                    $labelText = e($value->label);
                    
                    if (!empty($value->link_url)) {
                        $colorTextItems[] = '<a href="' . e($value->link_url) . '" class="product-banner-text-link" target="_blank" rel="noopener noreferrer nofollow">' . $labelText . '</a>';
                    } elseif (!$value->show_label && !$productBanner->hide_value_labels) {
                        $colorTextItems[] = $labelText;
                    }
                }
            @endphp
            @if (!empty($colorTextItems))
                <div class="product-banner-text-values">
                    {!! implode('<span class="product-banner-text-sep"> , </span>', $colorTextItems) !!}
                </div>
            @endif

        @else
            @php
                // Collect visible text items to render them comma-separated on one line.
                $textItems = [];
                foreach ($productBanner->values as $value) {
                    if (empty($value->label)) continue;
                    if (!empty($value->link_url)) {
                        $textItems[] = '<a href="' . e($value->link_url) . '" class="product-banner-text-link" target="_blank" rel="noopener noreferrer nofollow">' . e($value->label) . '</a>';
                    } elseif (!$value->show_label && !$productBanner->hide_value_labels) {
                        $textItems[] = e($value->label);
                    }
                }
            @endphp
            @if (!empty($textItems))
                <div class="product-banner-text-values">
                    {!! implode('<span class="product-banner-text-sep"> , </span>', $textItems) !!}
                </div>
            @endif
        @endif
    </div>
@endforeach
