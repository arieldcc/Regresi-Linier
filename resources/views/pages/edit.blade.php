@extends('layouts.admin.main')

@section('content')
<main class="content-wrapper container">
    <h4>Edit Halaman</h4>

    <form action="{{ route('halaman.update', $halaman->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Judul Halaman</label>
            <input type="text" name="title" value="{{ old('title', $halaman->title) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Konten</label>
            <textarea name="content" class="form-control" rows="6" required>{{ old('content', $halaman->content) }}</textarea>
        </div>

        <div class="mb-3">
            <label>Tampilkan Untuk Role</label>
            <select name="roles[]" multiple class="form-control">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ in_array($role->id, $halaman->roles->pluck('id')->toArray()) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</main>
@endsection
