<nav class="d-flex align-items-center justify-content-between py-3">
    <div>
        <h2 class="page-title mb-0">News Dashboard</h2>
        <small class="text-muted">Manage and view news content from News Service</small>
    </div>

    <div class="d-flex align-items-center">
        <ul class="nav me-3">
            {{-- <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Home</a></li> --}}
            <li class="nav-item"><a class="nav-link {{ request()->is('news*') ? 'active' : '' }}" href="/news">News Management</a></li>
        </ul>

        @include('components.header-right')

    </div>
</nav>
