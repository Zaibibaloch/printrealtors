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
                        <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy">

                        @if (!empty($value->label) && $value->show_label && !$productBanner->hide_value_labels)
                            <figcaption>{{ $value->label }}</figcaption>
                        @endif

                        @if (!empty($value->link_url))
                            <div class="product-banner-link-cta">
                                <a href="{{ $value->link_url }}" target="_blank" rel="noopener noreferrer nofollow">
                                    See more information
                                </a>
                            </div>
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
            @php $colorLink = optional($productBanner->values->firstWhere('link_url', '!=', null))->link_url; @endphp
            @if (!empty($colorLink))
                <div class="product-banner-link-cta">
                    <a href="{{ $colorLink }}" target="_blank" rel="noopener noreferrer nofollow">
                        See more information
                    </a>
                </div>
            @endif

        @else
            <ul class="list-inline form-custom-radio custom-selection">
                @foreach ($productBanner->values as $value)
                    <li>
                        @if ($value->show_label && !$productBanner->hide_value_labels)
                            {{ $value->label }}
                        @endif
                        @if (!empty($value->link_url))
                            <a href="{{ $value->link_url }}" class="product-banner-value-link" target="_blank" rel="noopener noreferrer nofollow">
                                See more information
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach
