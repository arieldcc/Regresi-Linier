@extends('layouts.admin.main')
@section('title', 'Permission')
@section('content')
<main class="content-wrapper container">
    <h4>Atur Akses untuk Role: {{ $role->name }}</h4>

    <form method="POST" action="{{ route('role.permissions.update', $role) }}">
        @csrf

        <div class="mb-3">
            @foreach($permissions as $permission)
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="permissions[]"
                           value="{{ $permission->id }}"
                           id="perm{{ $permission->id }}"
                           {{ $role->permissions->contains($permission) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm{{ $permission->id }}">
                        {{ ucfirst($permission->name) }}
                    </label>
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary">Simpan Akses</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</main>
@endsection
