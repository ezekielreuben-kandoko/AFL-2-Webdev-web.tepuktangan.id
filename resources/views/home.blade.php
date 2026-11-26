@extends('layouts.app')

@section('title', 'Tepuk Tangan | Home')

@push('head')
    <link
        rel="preload"
        as="image"
        href="{{ asset('assets/Home/opt/DIA00001-1200.webp') }}"
        imagesrcset="{{ asset('assets/Home/opt/DIA00001-480.webp') }} 480w, {{ asset('assets/Home/opt/DIA00001-800.webp') }} 800w, {{ asset('assets/Home/opt/DIA00001-1200.webp') }} 1200w, {{ asset('assets/Home/opt/DIA00001-1600.webp') }} 1600w"
        imagesizes="100vw"
        fetchpriority="high"
    />
    <style>
        .home-hero { position: relative; height: 100svh; min-height: 600px; overflow: hidden; background: #18211f; }
        .home-hero-track { display: flex; width: 100%; height: 100%; }
        .home-hero-slide { position: relative; flex: 0 0 100%; height: 100%; overflow: hidden; }
        .home-hero-slide img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center 42%; }
    </style>
@endpush

@section('content')
    <div class="home-page">
        <section class="home-hero" aria-label="Featured wedding stories">
            <div class="home-hero-track" aria-live="off">
                @foreach ($heroSlides as $index => $slide)
                    <article class="home-hero-slide">
                        <x-home-image
                            :file="$slide['file']"
                            alt="Wedding photography by Tepuk Tangan"
                            :sizes="$slide['sizes']"
                            loading="eager"
                            :fetchpriority="$index === 0 ? 'high' : 'auto'"
                        />
                    </article>
                @endforeach
            </div>
            <div class="home-hero-vignette" aria-hidden="true"></div>
        </section>

        <section class="home-intro" id="our-approach" aria-labelledby="home-intro-title">
            <div class="home-intro-copy">
                <h1 id="home-intro-title">Where fleeting golden hours become classic, living heirlooms.</h1>
                <div class="home-intro-story">
                    <p class="home-eyebrow">Who we are</p>
                    <p>We are a collective of passionate, creative photographers and videographers collaborating together to realize your once-in-a-lifetime story. Founded in January 2020 in Indonesia, Tepuk Tangan have continuously challenged our team to capture your golden moments—and it is always a genuine pleasure to preserve every frame.</p>
                </div>
                <div class="home-intro-story home-intro-belief">
                    <p class="home-eyebrow">Our believe</p>
                    <p>Rooted in a simple, classic, and timeless style, we deeply value how our creative minds grow-nurturing our craft, emotions, and passion together. We believe in the quiet magic of capturing every beautiful moment through each artist's unique strengths.</p>
                </div>
            </div>
            <figure class="home-intro-figure">
                <x-home-image file="DIA00056.jpg" alt="Wedding photography by Tepuk Tangan" class="home-intro-portrait" sizes="(max-width: 680px) 100vw, 440px" grayscale />
                <x-home-image file="DIA00223.jpg" alt="" aria-hidden="true" class="home-intro-detail" sizes="(max-width: 680px) 33vw, 160px" grayscale />
            </figure>
        </section>

        <section class="home-darkroom" id="selected-works" aria-labelledby="home-darkroom-title">
            <div class="home-darkroom-inner">
                <header class="home-darkroom-header">
                    <div>
                        <p class="home-eyebrow">A living archive, made together</p>
                        <h2 id="home-darkroom-title">Golden Moments<br>&amp; Motion</h2>
                    </div>
                </header>

                <div class="home-gallery-grid">
                    @foreach ($galleryPhotos as $index => $file)
                        <a class="home-gallery-card home-gallery-card--{{ $index + 1 }}" href="{{ route('gallery') }}">
                            <x-home-image :file="$file" alt="Wedding photography by Tepuk Tangan" sizes="(max-width: 680px) 50vw, (max-width: 1100px) 45vw, 620px" muted />
                            <span><i>Photography by Tepuk Tangan</i></span>
                        </a>
                    @endforeach

                    <a class="home-gallery-card home-gallery-card--film" href="{{ route('gallery') }}">
                        <x-home-image file="DIA08889.jpg" alt="Wedding Film" sizes="(max-width: 680px) 100vw, (max-width: 1100px) 66vw, 900px" />
                        <span class="home-gallery-film-mark" aria-hidden="true">Play film ↗</span>
                    </a>
                </div>
                <a class="home-archive-link" href="{{ route('gallery') }}">Explore all stories <span aria-hidden="true">⟶</span></a>
            </div>
        </section>

        <section class="home-collage" aria-labelledby="home-cta-title">
            @foreach ($collageFrames as $frame)
                <div class="home-collage-frame home-collage-frame--{{ $frame['class'] }}">
                    <x-home-image :file="$frame['file']" alt="" aria-hidden="true" sizes="(max-width: 680px) 45vw, 220px" grayscale />
                </div>
            @endforeach
            <div class="home-collage-copy">
                <p class="home-eyebrow">Crafted for once in a lifetime</p>
                <h2 id="home-cta-title"><span>Golden moments pass</span> <span>in a heartbeat.</span> <span>Let’s make yours</span> <span>stay forever.</span></h2>
                <p class="home-collage-invitation">Kindly share your magical ideas or dreams with us—we would love to hear from you and make them happen.</p>
                <a class="home-cta-link" href="{{ route('inquiry') }}">Share your dream with us <span aria-hidden="true">⟶</span></a>
            </div>
        </section>

    </div>
@endsection
