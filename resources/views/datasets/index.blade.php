@extends('layouts.admin.main')
@section('title', 'Dataset')
@section('content')
<main class="content-wrapper container">
    <h4>Daftar Dataset</h4>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <a href="{{ route('dataset.create') }}" class="btn btn-primary mb-3">+ Tambah Dataset</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Keterangan</th>
                <th>File</th>
                <th>Variabel X</th>
                <th>Variabel Y</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datasets as $dataset)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $dataset->name }}</td>
                <td>{{ $dataset->description }}</td>
                <td>
                    <a href="{{ asset('storage/' . $dataset->file_path) }}" target="_blank">Download</a>
                </td>
                <td>{{ $dataset->x_variable }}</td>
                <td>{{ $dataset->y_variable }}</td>
                <td>
                    <a href="{{ route('dataset.show', $dataset->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('dataset.edit', $dataset->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('dataset.destroy', $dataset->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection
