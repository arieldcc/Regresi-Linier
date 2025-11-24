{{-- resources/layouts/admin/partials/navbar.blade.php --}}
@php
    use App\Models\Role;

    if (Auth::check()) {
        $permissions = Auth::user()->role?->permissions->sortByDesc('urut') ?? collect();
        $roleName = Auth::user()->role?->name;
    } else {
        $guestRole = Role::where('name', 'guest')->with('permissions')->first();
        $permissions = $guestRole?->permissions->sortByDesc('urut') ?? collect();
        $roleName = $guestRole?->name ?? 'guest';
    }
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">{{ ucfirst($roleName) }}</a>
        {{-- @auth
            <a class="navbar-brand" href="dashboard"><span class="navbar-brand disabled">Role: {{ Auth::user()->role->name }}</span></a>
        @endauth --}}
        <ul class="navbar-nav ms-auto">
        @foreach ($permissions as $permission)
            <li class="nav-item">
                <a class="nav-link"
                href="{{ Route::has($permission->route) ? route($permission->route) : url('/under-construction') }}">
                    {{ ucfirst($permission->name) }}
                </a>
            </li>
        @endforeach
        @if(!Auth::check())
            <li class="nav-item">
                <a href="{{ route('login.form') }}" class="btn btn-outline-light">Login</a>
            </li>
        @else
            <li class="nav-item">
                <a href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="btn btn-outline-danger">
                    Logout
                </a>
            </li>
        @endif
        </ul>
        </div>
    </div>
</nav>


<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>
