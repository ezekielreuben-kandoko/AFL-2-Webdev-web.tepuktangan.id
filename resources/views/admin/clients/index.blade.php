@extends('layouts.app-admin')

@section('title', 'Kelola Klien | Admin Tepuk Tangan')

@section('content')
    @include('components.navbar_admin')

    <section class="" style="padding: clamp(48px, 7vw, 88px) clamp(64px, 9vw, 112px);">
        <div class="home-intro-copy" style="max-width:1000px;margin:0 auto;">
            @if (session('success'))
                <div style="background:#dcfce7;color:#166534;padding:12px;border-radius:8px;margin-bottom:24px;">{{ session('success') }}</div>
            @endif

            <h1 style="font-family:'Bodoni Moda',Georgia,serif;font-size:clamp(32px,5vw,48px);line-height:1.1;margin:0 0 32px;">Kelola Klien</h1>

            <div class="gallery-card-grid-admin">
                @forelse ($clients as $client)
                    <a href="{{ route('admin.clients.assets', $client) }}" class="gallery-card" style="text-decoration:none;display:block;position:relative;overflow:hidden;border-radius:8px;box-shadow:0 4px 14px rgb(24 34 15 / 18%);background:#2b2a26;">
                        <span class="gallery-card__cover" style="display:block;aspect-ratio:1;">
                            @if ($client->cover_url)
                                <img src="{{ $client->cover_url }}" alt="{{ $client->client_name }}" referrerpolicy="no-referrer" style="width:100%;height:100%;object-fit:cover;display:block;">
                            @else
                                <div style="width:100%;height:100%;background:#4a4438;display:flex;align-items:center;justify-content:center;color:#8a8274;font-size:14px;">No Image</div>
                            @endif
                            <span class="gallery-card__shade" aria-hidden="true" style="position:absolute;inset:0;background:rgba(0,0,0,0.3);opacity:0;transition:opacity 0.3s;"></span>
                        </span>

                        <span class="gallery-card__meta" style="padding:16px;display:block;">
                            <span class="gallery-card__title" style="font-family:'Bodoni Moda',Georgia,serif;font-size:18px;color:#fff;display:block;margin-bottom:4px;">{{ $client->client_name }}</span>
                            <span style="font-size:12px;color:#c5a059;display:block;">{{ ucfirst($client->client_type) }}</span>
                            @if ($client->month_date)
                                <span style="font-size:12px;color:#8a8274;display:block;">{{ $client->month_date }}</span>
                            @endif
                        </span>

                        <div style="position:absolute;top:8px;right:8px;display:flex;gap:8px;opacity:0;transition:opacity 0.3s;">
                            <a href="{{ route('admin.clients.edit', $client) }}" style="background:#c5a059;color:#fff;padding:6px 10px;border-radius:4px;text-decoration:none;font-size:12px;">Edit</a>
                            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" style="margin:0;display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus klien ini? Aset terkait juga akan terhapus.')" style="background:#dc2626;color:#fff;padding:6px 10px;border-radius:4px;border:none;cursor:pointer;font-size:12px;">Hapus</button>
                            </form>
                        </div>
                    </a>
                @empty
                    <p style="text-align:center;color:#55524b;">Belum ada klien. Tambahkan klien baru di atas.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.gallery-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.querySelector('.gallery-card__shade')?.classList.add('opacity-100') || card.querySelector('.gallery-card__shade')?.style.setProperty('opacity', '1');
            card.querySelector('div[style*="position:absolute;top:8px;right:8px"]')?.style.setProperty('opacity', '1');
        });
        card.addEventListener('mouseleave', () => {
            card.querySelector('.gallery-card__shade')?.style.setProperty('opacity', '0.3');
            card.querySelector('div[style*="position:absolute;top:8px;right:8px"]')?.style.setProperty('opacity', '0');
        });
    });
</script>
@endpush
