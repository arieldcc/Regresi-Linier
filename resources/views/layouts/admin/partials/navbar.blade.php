{{-- resources/layouts/admin/partials/navbar.blade.php --}}

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        {{-- <a class="navbar-brand" href="#">Admin Panel</a> --}}
        @php
            $permissions = Auth::user()?->role?->permissions->sortByDesc('urut') ?? [];
        @endphp
        @auth
            <a class="navbar-brand" href="dashboard"><span class="navbar-brand disabled">Role: {{ Auth::user()->role->name }}</span></a>
        @endauth
        <ul class="navbar-nav ms-auto">
        @foreach ($permissions as $permission)
            <li class="nav-item">
                <a class="nav-link"
                href="{{ Route::has($permission->route) ? route($permission->route) : url('/under-construction') }}">
                    {{ ucfirst($permission->name) }}
                </a>
            </li>
        @endforeach
            <li class="nav-item">
                <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="btn btn-outline-danger">
                    Logout
                </a>
            </li>

            {{-- <a class="nav-link"
                href="{{ route('dataset.index') }}">
                    Dataset
                </a> --}}
        </ul>
        </div>
    </div>
</nav>


<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>
