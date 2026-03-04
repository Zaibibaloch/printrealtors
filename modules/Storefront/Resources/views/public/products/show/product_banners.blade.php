@foreach ($productBanners as $productBanner)
    @if ($productBanner->values->isEmpty())
        @continue
    @endif

    @php
        // Use a single banner-level URL (if any value has link_url).
        $bannerLink = optional($productBanner->values->firstWhere('link_url', '!=', null))->link_url;
    @endphp

    <div class="product-banner-block">
        @unless ($productBanner->hide_title)
            <div class="product-banner-title">
                {{ $productBanner->name }}:
            </div>
        @endunless

        @if (in_array($productBanner->type, ['image', 'design'], true))
            @php
                $bannerImages = [];
                foreach ($productBanner->values as $v) {
                    $m = $productBanner->type === 'design' ? ($v->design ?? null) : ($v->image ?? null);
                    if ($m && !empty($m->path)) {
                        $bannerImages[] = ['src' => $m->path, 'label' => $v->label ?? ''];
                    }
                }
                $bannerId = 'pb-' . $productBanner->id;
            @endphp

            <div class="product-banner-media-grid">
                @php $renderIdx = 0; @endphp
                @foreach ($productBanner->values as $value)
                    @php
                        $media = $productBanner->type === 'design' ? ($value->design ?? null) : ($value->image ?? null);
                    @endphp

                    @if (!$media || empty($media->path))
                        @continue
                    @endif

                    <figure
                        class="product-banner-media-item product-banner-media-item--clickable"
                        onclick="window.pbLightboxOpen({{ $renderIdx }}, {{ json_encode($bannerImages) }})"
                        style="cursor:zoom-in;"
                    >
                        <img src="{{ $media->path }}" alt="{{ $value->label ?: $productBanner->name }}" loading="lazy">

                        @if (!empty($value->label) && $value->show_label && !$productBanner->hide_value_labels)
                            <figcaption>{{ $value->label }}</figcaption>
                        @endif
                    </figure>
                    @php $renderIdx++; @endphp
                @endforeach
            </div>
            @if (!empty($bannerLink))
                <div class="product-banner-link-cta">
                    <a href="{{ $bannerLink }}" target="_blank" rel="noopener noreferrer nofollow">
                        See more information
                    </a>
                </div>
            @endif
        @elseif ($productBanner->type === 'color')
            <ul class="list-inline form-custom-radio custom-selection product-banner-color-list">
                @foreach ($productBanner->values as $value)
                    <li class="variation-color">
                        <div style="background-color: {{ $value->color }};"></div>
                    </li>
                @endforeach
            </ul>
            @if (!empty($bannerLink))
                <div class="product-banner-link-cta">
                    <a href="{{ $bannerLink }}" target="_blank" rel="noopener noreferrer nofollow">
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
                    </li>
                @endforeach
            </ul>
            @if (!empty($bannerLink))
                <div class="product-banner-link-cta">
                    <a href="{{ $bannerLink }}" target="_blank" rel="noopener noreferrer nofollow">
                        See more information
                    </a>
                </div>
            @endif
        @endif
    </div>
@endforeach
