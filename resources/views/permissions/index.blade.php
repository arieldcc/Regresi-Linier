@extends('layouts.admin.main')
@section('title', 'Permission')
@section('content')
<main class="content-wrapper container">
    <h4>Daftar Permission (Menu)</h4>
    {{-- <a href="{{ route('role.permissions.edit', 1) }}" class="btn btn-secondary mb-3">Atur Akses Role</a> --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('status'))
        <div class="alert alert-{{ str_contains(session('status'), '❌') ? 'danger' : 'success' }}">
            {{ session('status') }}
        </div>
    @endif
    <a href="{{ route('permissions.create') }}" class="btn btn-primary mb-3">Tambah Menu</a>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Route</th>
                <th>Nomor Urut</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($permissions as $permission)
            <tr>
                <td>{{ $permission->id }}</td>
                <td>{{ $permission->name }}</td>
                <td>{{ $permission->route }}</td>
                <td>{{ $permission->urut }}</td>
                <td>
                    <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form method="POST" action="{{ route('permissions.destroy', $permission) }}" class="d-inline">
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
