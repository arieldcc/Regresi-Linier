@extends('layouts.admin.main')
@section('title', 'Dataset')

@section('content')
<main class="content-wrapper container">
    <form method="POST" action="{{ route('dataset.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Nama Dataset</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>File Excel</label>
            <input type="file" name="dataset_file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Upload</button>
    </form>
</main>
@endsection
