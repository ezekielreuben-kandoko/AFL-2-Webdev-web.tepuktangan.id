@props([
    'file',
    'alt' => '',
    'sizes' => '100vw',
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'grayscale' => false,
    'muted' => false,
])

@php
    $name = pathinfo($file, PATHINFO_FILENAME);
    $suffix = $grayscale ? '-gray' : ($muted ? '-muted' : '');
    $srcset = collect([480, 800, 1200, 1600])
        ->map(fn (int $width) => [
            'width' => $width,
            'path' => "assets/Home/opt/{$name}-{$width}{$suffix}.webp",
        ])
        ->filter(fn (array $variant) => is_file(public_path($variant['path'])))
        ->map(fn (array $variant) => asset($variant['path']).' '.$variant['width'].'w')
        ->implode(', ');
@endphp

<img
    src="{{ asset('assets/Home/'.$file) }}"
    @if ($srcset !== '') srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
    alt="{{ $alt }}"
    loading="{{ $loading }}"
    fetchpriority="{{ $fetchpriority }}"
    decoding="async"
    {{ $attributes }}
>
