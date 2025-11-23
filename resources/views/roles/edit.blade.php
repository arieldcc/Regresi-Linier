@extends('layouts.admin.main')
@section('title', 'Role User')

@section('content')
<main class="content-wrapper container">
<h4>{{ isset($role) ? 'Edit' : 'Tambah' }} Level</h4>

    <form method="POST" action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}">
        @csrf
        @if(isset($role)) @method('PUT') @endif

        <div class="mb-3">
            <label>Nama Level</label>
            <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</main>
@endsection
