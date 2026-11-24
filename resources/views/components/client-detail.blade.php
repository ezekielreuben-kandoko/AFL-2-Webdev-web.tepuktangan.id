<div class="client-detail">
    <header class="client-detail__header">
        <h1 class="client-detail__title">{{ $client->client_name }}</h1>
        @if ($client->month_date)
            <p class="client-detail__meta">
                <span>{{ $client->month_date }}</span>
            </p>
        @endif
    </header>

    <div class="client-detail__media">
        @foreach ($client->videos as $asset)
            @if ($asset->drive_file_id)
                <div class="client-detail__video" style="width:100%;aspect-ratio:16/9;">
                    <iframe
                        src="{{ $asset->embed_url }}"
                        allow="autoplay; fullscreen; encrypted-media; picture-in-picture"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer"
                        style="width:100%;height:100%;border:0;border-radius:8px;display:block;"
                        title="{{ $asset->alt ?: $client->client_name }}"
                    ></iframe>
                </div>
            @else
                <video class="client-detail__video" controls playsinline preload="none" aria-label="{{ $client->client_name }} wedding video">
                    <source src="{{ $asset->url }}">
                </video>
            @endif
        @endforeach

        @if ($client->photos->isNotEmpty())
            <div class="client-detail__photos">
                @foreach ($client->photos as $asset)
                    @php
                        $isFeature = $loop->first;
                        $imageUrl = $isFeature ? $asset->url : $asset->thumbnail_url;
                    @endphp

                    <img
                        class="client-detail__photo{{ $isFeature ? ' client-detail__photo--feature' : ' js-lazy-image' }}"
                        src="{{ $imageUrl }}"
                        @if (! $isFeature) data-src="{{ $imageUrl }}" @endif
                        alt="{{ $asset->alt ?: $client->client_name }}"
                        referrerpolicy="no-referrer"
                        loading="{{ $isFeature ? 'eager' : 'lazy' }}"
                        fetchpriority="{{ $isFeature ? 'high' : 'low' }}"
                        decoding="async"
                    >
                @endforeach
            </div>
        @endif
    </div>

    @if ($client->videos->isEmpty() && $client->photos->isEmpty())
        <p class="client-detail__empty">Belum ada aset untuk klien ini.</p>
    @endif

    <a class="client-detail__back" href="{{ route($listingRoute) }}">Kembali</a>
</div>
