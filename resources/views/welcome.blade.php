@extends('layouts.admin.main')

@section('title', 'Dashboard')

@section('content')
@include('layouts.admin.partials.header')

<main class="content-wrapper container">
  <h1>Dashboard {{ Auth::user()->role->name }}</h1>
  <p>Selamat datang di panel administrasi PDAM.</p>
    @php
$pages = \App\Models\Page::whereHas('roles', function ($q) {
    $q->where('roles.id', auth()->user()->role_id);
})->get();
@endphp

    @foreach($pages as $page)
        <div class="card my-3">
            <div class="card-header">{{ $page->title }}</div>
            <div class="card-body">{!! nl2br(e($page->content)) !!}</div>
        </div>
    @endforeach
</main>
@endsection
