@extends('layouts.admin.main')
@section('title', 'Edit Dataset')

@section('content')
<main class="content-wrapper container">
    <form method="POST" action="{{ route('dataset.update', $dataset->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nama Dataset</label>
            <input type="text" name="name" class="form-control" value="{{ $dataset->name }}" required>
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="description" class="form-control">{{ $dataset->description }}</textarea>
        </div>

        <div class="mb-3">
            <label>Ganti File (opsional)</label>
            <input type="file" name="dataset_file" class="form-control">
        </div>

        <div class="mb-3">
            <label>Pilih Variabel X</label><br>
            @foreach ($headers as $header)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="x_variable[]" value="{{ $header }}"
                        {{ in_array($header, json_decode($dataset->x_variable)) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $header }}</label>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label>Pilih Variabel Y</label><br>
            @foreach ($headers as $header)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="y_variable[]" value="{{ $header }}"
                        {{ in_array($header, json_decode($dataset->y_variable)) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $header }}</label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Update Dataset</button>
    </form>
</main>
@endsection
