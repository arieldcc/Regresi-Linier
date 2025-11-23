@extends('layouts.admin.main')
@section('title', 'Tambah Halaman')

@section('content')
<main class="content-wrapper container">
    <h4>Tambah Halaman Baru</h4>

    <form action="{{ route('halaman.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Judul Halaman</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Konten Halaman</label>
            <textarea name="content" class="form-control" rows="8" required>{{ old('content') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Tampilkan untuk Role</label><br>
            @foreach($roles as $role)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}">
                    <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('halaman.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</main>
@endsection
