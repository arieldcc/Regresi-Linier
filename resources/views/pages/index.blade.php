@extends('layouts.admin.main')
@section('title', 'Kelola Halaman')

@section('content')
<main class="content-wrapper container">
    <h4>Daftar Halaman</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('halaman.create') }}" class="btn btn-primary mb-3">Tambah Halaman</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Konten</th>
                <th>Role yang Bisa Melihat</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pages as $page)
                <tr>
                    <td>{{ $page->title }}</td>
                    <td>{!! Str::limit(strip_tags($page->content), 100, '...') !!}</td>
                    <td>
                        @foreach ($page->roles as $role)
                            <span class="badge bg-secondary">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <a href="{{ route('halaman.edit', $page) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('halaman.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus halaman ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada halaman yang dibuat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</main>
@endsection
