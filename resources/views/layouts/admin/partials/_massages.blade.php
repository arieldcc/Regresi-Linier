{{-- resources/layouts/admin/partials/_massages.blade.php --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Terjadi kesalahan:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li><i class="bi bi-x-circle"></i> {{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
