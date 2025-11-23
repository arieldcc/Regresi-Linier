@extends('layouts.admin.main')
@section('title', 'User')

@section('content')
<main class="content-wrapper container">
    <h4>{{ isset($user) ? 'Edit' : 'Tambah' }} User</h4>

    <form method="POST" action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Role</label>
            <select name="role_id" class="form-select" required>
                <option value="">Pilih Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((old('role_id', $user->role_id ?? '') == $role->id))>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Password {{ isset($user) ? '(opsional)' : '' }}</label>
            <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-control" {{ isset($user) ? '' : 'required' }}>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</main>
@endsection
