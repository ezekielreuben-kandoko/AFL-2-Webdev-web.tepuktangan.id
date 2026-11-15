<nav class="nav admin-nav" aria-label="Navigasi admin">
    <a href="{{ route('admin.clients.index') }}" class="nav__brand">
        <span class="nav__brand-logo">
            <img src="{{ asset('assets/Logo/tepuktangan_white.png') }}" alt="Tepuk Tangan" />
        </span>
    </a>
    <div class="nav__menu">
        <a href="{{ route('admin.clients.index') }}" class="nav__link">Kembali ke Daftar Klien</a>
        <a href="{{ route('admin.clients.create') }}" class="nav__link">Tambah Klien Baru</a>
        <a href="#" class="nav__link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
            @csrf
        </form>
    </div>
</nav>
