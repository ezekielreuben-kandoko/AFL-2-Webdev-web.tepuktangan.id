@extends('layouts.app-admin')

@section('title', 'Aset ' . $client->client_name . ' | Admin Tepuk Tangan')

@section('content')
    @include('components.navbar_admin')

    <section class="" style="padding: clamp(48px, 7vw, 88px) clamp(64px, 9vw, 112px);">
        <div class="home-intro-copy">
            @if (session('success'))
                <div
                    style="background:#dcfce7;color:#166534;padding:12px;border-radius:8px;margin-bottom:24px;">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div
                    style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:24px;">{{ session('error') }}</div>
            @endif

            @if (session('warning'))
                <div
                    style="background:#fef3c7;color:#92400e;padding:12px;border-radius:8px;margin-bottom:24px;">{{ session('warning') }}</div>
            @endif

            <h1 style="font-family:'Bodoni Moda',Georgia,serif;font-size:clamp(32px,5vw,48px);line-height:1.1;margin:0 0 32px;">
                Aset: {{ $client->client_name }}</h1>

            <div style="background:#f5f2ec;border:1px solid #d4cfc7;border-radius:8px;padding:20px;margin-bottom:32px;">
                <h2 style="font-family:'Bodoni Moda',Georgia,serif;font-size:20px;margin:0 0 12px;color:#2b2a26;">Sumber
                    Google Drive</h2>

                @if ($client->driveSources->isNotEmpty())
                    <ul style="margin:0 0 16px;padding-left:20px;font-size:14px;color:#2b2a26;">
                        @foreach ($client->driveSources as $source)
                            <li style="margin-bottom:6px;word-break:break-all;">
                                <a href="{{ $source->url }}" target="_blank" rel="noopener"
                                   style="color:#263317;">{{ $source->url }}</a>
                                <span style="color:#8a8274;font-size:12px;">({{ $source->link_type }})</span>
                            </li>
                        @endforeach
                    </ul>
                    <p style="margin:0 0 16px;font-size:13px;color:#8a8274;">
                        Terakhir sync:
                        {{ $client->drive_synced_at ? $client->drive_synced_at->format('d M Y H:i') : 'Belum pernah' }}
                        · {{ $client->assets->count() }} aset
                    </p>
                    <form method="POST" action="{{ route('admin.clients.assets.sync', $client) }}">
                        @csrf
                        <button type="submit"
                                style="background:#263317;color:#fff;border:none;padding:12px 24px;border-radius:6px;font-family:'Bodoni Moda',Georgia,serif;font-size:16px;cursor:pointer;">
                            Sync dari Google Drive
                        </button>
                    </form>
                @else
                    <p style="margin:0;font-size:14px;color:#8a8274;">
                        Belum ada link Google Drive.
                        <a href="{{ route('admin.clients.edit', $client) }}" style="color:#263317;font-weight:600;">Edit
                            klien</a> untuk menambahkan.
                    </p>
                @endif
            </div>

            <a href="{{ route('admin.clients.edit', $client) }}" class="nav__link"
               style="display:inline-block;margin-top:8px;padding:8px 16px;cursor:pointer;font-weight:600;background-color: orange;border:1px solid orange;">Edit
                Klien</a>
            <h2 style="font-family:'Bodoni Moda',Georgia,serif;font-size:24px;margin:48px 0 24px;color:#2b2a26;">Aset
                Saat Ini</h2>
            <div class="gallery-card-grid-admin">
                @forelse ($client->assets->sortBy('sort_order') as $asset)
                    <div style="position:relative;overflow:hidden;border-radius:8px;background:#2b2a26;aspect-ratio:1;">
                        @if ($asset->file_type === 'video')
                            <img
                                src="{{ $asset->thumbnail_url }}"
                                alt="{{ $asset->alt ?: $client->client_name }}"
                                referrerpolicy="no-referrer"
                                loading="lazy"
                                style="width:100%;height:100%;object-fit:cover;display:block;"
                            >
                            <span
                                style="position:absolute;left:8px;bottom:8px;background:rgba(0,0,0,.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:11px;letter-spacing:.08em;">VIDEO</span>
                        @else
                            <img
                                src="{{ $asset->url }}"
                                alt="{{ $asset->alt ?: $client->client_name }}"
                                referrerpolicy="no-referrer"
                                loading="lazy"
                                style="width:100%;height:100%;object-fit:cover;display:block;"
                            >
                        @endif
                        <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}"
                              style="position:absolute;top:8px;right:8px;margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Hapus aset ini dari website? File di Google Drive tidak akan terhapus.')"
                                    style="background:#dc2626;color:#fff;border:none;padding:6px 10px;border-radius:4px;cursor:pointer;font-size:12px;">
                                Hapus dari web
                            </button>
                        </form>
                    </div>
                @empty
                    <p style="text-align:center;color:#55524b;grid-column:1/-1;">Belum ada aset. Tambahkan link Google
                        Drive lalu klik Sync.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
