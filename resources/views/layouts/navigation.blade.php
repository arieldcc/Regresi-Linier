<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">Admin Panel</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            @auth
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/data') }}">Data Penggunaan Air</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/prediksi') }}">Prediksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/grafik') }}">Grafik Prediksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-link nav-link" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            @endauth

            @guest
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                </ul>
            @endguest
        </div>
    </div>
</nav>
