@extends('layouts.admin.main')
@section('title', 'User')

@section('content')
<main class="content-wrapper container">
    <h4>Daftar User</h4>
    @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Tambah User</a>

    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role->name ?? '-' }}</td>
                <td>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
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
