@extends('layouts.admin.main')
@section('title', 'Role User')
@section('content')
<main class="content-wrapper container">
    <h4>Level User</h4>
    @if(session('status'))
        <div class="alert alert-{{ str_contains(session('status'), '❌') ? 'danger' : 'success' }}">
            {{ session('status') }}
        </div>
    @endif
    <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">Tambah Level</a>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Nama</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach($roles as $role)
            <tr>
                <td>{{ $role->id }}</td>
                <td>{{ $role->name }}</td>
                <td>
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('role.permissions.edit', $role) }}" class="btn btn-info btn-sm">Atur Akses</a>
                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Yakin?')">
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
