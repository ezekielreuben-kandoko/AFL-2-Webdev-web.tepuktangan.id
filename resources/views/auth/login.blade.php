@extends('layouts.app')

@section('title', 'Login Admin | Tepuk Tangan')

@section('content')
    <section class="home-intro" style="padding: clamp(48px, 7vw, 88px) clamp(64px, 9vw, 112px);">
        <div class="home-intro-copy" style="max-width: 420px; margin: 0 auto;">
            @if (session('error'))
                <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:20px;">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:20px;">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h1 style="font-family:'Bodoni Moda',Georgia,serif;font-size:clamp(32px,5vw,48px);line-height:1.1;margin:0 0 24px;">Masuk Admin</h1>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div style="margin-bottom:20px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:16px;">
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;margin-bottom:6px;font-size:14px;color:#2b2a26;">Password</label>
                    <input type="password" name="password" required style="width:100%;padding:12px;border:1px solid #d4cfc7;border-radius:6px;font-family:inherit;font-size:16px;">
                </div>

                <button type="submit" style="background:#263317;color:#fff;border:none;padding:14px 24px;border-radius:6px;font-family:'Bodoni Moda',Georgia,serif;font-size:18px;cursor:pointer;width:100%;">Masuk</button>
            </form>
        </div>
    </section>
@endsection