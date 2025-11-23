@extends('layouts.admin.main')
@section('title', 'Permission')
@section('content')
<main class="content-wrapper container">
    <h4>{{ isset($permission) ? 'Edit' : 'Tambah' }} Menu (Permission)</h4>

    <form method="POST" action="{{ isset($permission) ? route('permissions.update', $permission) : route('permissions.store') }}">
        @csrf
        @if(isset($permission)) @method('PUT') @endif

        <div class="mb-3">
            <label>Nama Menu</label>
            <input type="text" name="name" value="{{ old('name', $permission->name ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Route</label>
            <input type="text" name="route" value="{{ old('route', $permission->route ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>No. Urut</label>
            <input type="number" name="urut"
                value="{{ old('urut', $permission->urut ?? $nextUrut ?? '') }}"
                class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</main>
@endsection
