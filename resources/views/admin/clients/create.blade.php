@extends('layouts.app-admin')

@section('title', 'Tambah Klien | Admin Tepuk Tangan')

@section('content')
    @include('components.navbar_admin')

    <section class="home-intro" style="padding: clamp(48px, 7vw, 88px) clamp(64px, 9vw, 112px);">
        <div class="home-intro-copy" style="max-width:600px;margin:0 auto;">
            @if (session('success'))
                <div style="background:#dcfce7;color:#166534;padding:12px;border-radius:8px;margin-bottom:24px;">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:24px;">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h1 style="font-family:'Bodoni Moda',Georgia,serif;font-size:clamp(32px,5vw,48px);line-height:1.1;margin:0 0 32px;">Tambah Klien Baru</h1>

            <form method="POST" action="{{ route('admin.clients.store') }}">
                @csrf

                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Nama Klien</label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:16px;">
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Tipe Klien</label>
                    <select name="client_type" required style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:16px;">
                        <option value="gallery" {{ old('client_type') === 'gallery' ? 'selected' : '' }}>Gallery (Fotografi)</option>
                        <option value="photobooth" {{ old('client_type') === 'photobooth' ? 'selected' : '' }}>Photobooth</option>
                        <option value="lumenara" {{ old('client_type') === 'lumenara' ? 'selected' : '' }}>Lumenara</option>
                        <option value="livestream" {{ old('client_type') === 'livestream' ? 'selected' : '' }}>Livestreaming</option>
                    </select>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Bulan / Tanggal (contoh: Januari 2024)</label>
                    <input type="text" name="month_date" value="{{ old('month_date') }}" placeholder="Januari 2024" style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:16px;">
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Link Google Drive (satu link per baris)</label>
                    <textarea name="drive_links" rows="4" placeholder="https://drive.google.com/drive/folders/...&#10;https://drive.google.com/file/d/..." style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:15px;resize:vertical;">{{ old('drive_links') }}</textarea>
                    <p style="margin:6px 0 0;font-size:13px;color:#8a8274;">Setel folder/file ke "Anyone with the link". Semua foto &amp; video diambil otomatis saat disimpan.</p>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Link Cover (opsional)</label>
                    <input type="url" name="cover_drive_url" value="{{ old('cover_drive_url') }}" placeholder="https://drive.google.com/file/d/..." style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:16px;">
                    <p style="margin:6px 0 0;font-size:13px;color:#8a8274;">Kalau dikosongkan, cover otomatis memakai foto pertama dari folder.</p>
                </div>

                <button type="submit" style="background:#263317;color:#fff;border:none;padding:14px 24px;border-radius:6px;font-family:'Bodoni Moda',Georgia,serif;font-size:18px;cursor:pointer;width:100%;">Simpan Klien</button>
            </form>
        </div>
    </section>
@endsection
