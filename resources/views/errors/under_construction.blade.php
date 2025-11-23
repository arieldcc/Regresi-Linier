@extends('layouts.admin.main')
@section('title', 'Under Construction!')

@section('content')
<main class="content-wrapper container">
    <div class="container text-center mt-5">
        <h2>🚧 Menu “{{ ucfirst($menu) }}” belum tersedia</h2>
        <p>Fitur ini masih dalam pengembangan atau belum ditambahkan oleh admin.</p>
        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</main>
@endsection
