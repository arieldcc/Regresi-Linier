@extends('layouts.admin.main')

@section('title', 'Dashboard')

@section('content')
@include('layouts.admin.partials.header')

<main class="content-wrapper container">
  <h1>Dashboard {{ ucfirst($roleName) }}</h1>
  <p>Selamat datang di panel administrasi PDAM.</p>

  @forelse($pages as $page)
      <div class="card my-3">
          <div class="card-header">{{ $page->title }}</div>
          <div class="card-body">{!! nl2br(e($page->content)) !!}</div>
      </div>
  @empty
      <div class="alert alert-info mt-3">
          Tidak ada halaman yang tersedia untuk role ini.
      </div>
  @endforelse
</main>
@endsection
