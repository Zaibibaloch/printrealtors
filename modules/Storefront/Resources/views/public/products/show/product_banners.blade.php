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
                            <a href="{{ $value->link_url }}" class="product-banner-link" target="_blank" rel="noopener noreferrer nofollow">
                                <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy">
                            </a>
                        @else
                            <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy">
                        @endif

                        @if (!empty($value->label) && $value->show_label)
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
                    @if (!empty($value->link_url))
                        <li class="variation-color">
                            <a href="{{ $value->link_url }}" class="product-banner-link" target="_blank" rel="noopener noreferrer nofollow">
                                <div style="background-color: {{ $value->color }};"></div>
                            </a>
                            <div class="product-banner-link-cta">
                                <a href="{{ $value->link_url }}" target="_blank" rel="noopener noreferrer nofollow">
                                    See more information
                                </a>
                            </div>
                        </li>
                    @else
                        <li class="variation-color">
                            <div style="background-color: {{ $value->color }};"></div>
                        </li>
                    @endif
                @endforeach
            </ul>
        @else
            <ul class="list-inline form-custom-radio custom-selection">
                @foreach ($productBanner->values as $value)
                    <li>
                        @if (!empty($value->link_url))
                            <a href="{{ $value->link_url }}" class="product-banner-link" target="_blank" rel="noopener noreferrer nofollow">
                                @if ($value->show_label)
                                    {{ $value->label }}
                                @endif
                            </a>
                            <div class="product-banner-link-cta">
                                <a href="{{ $value->link_url }}" target="_blank" rel="noopener noreferrer nofollow">
                                    See more information
                                </a>
                            </div>
                        @else
                            @if ($value->show_label)
                                {{ $value->label }}
                            @endif
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach
