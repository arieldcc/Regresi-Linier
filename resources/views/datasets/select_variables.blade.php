@extends('layouts.admin.main')
@section('title', 'Pilih Variabel')

@section('content')
<main class="content-wrapper container">
    <form method="POST" action="{{ route('dataset.finalize') }}">
        @csrf
        <input type="hidden" name="file_path" value="{{ $file_path }}">
        <input type="hidden" name="name" value="{{ $name }}">
        <input type="hidden" name="description" value="{{ $description }}">

        <div class="mb-3">
            <label><strong>Pilih Variabel X</strong></label><br>
            @foreach ($headers as $header)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="x_variable[]" value="{{ $header }}" id="x_{{ $loop->index }}">
                    <label class="form-check-label" for="x_{{ $loop->index }}">{{ $header }}</label>
                </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label><strong>Pilih Variabel Y</strong></label><br>
            @foreach ($headers as $header)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="y_variable[]" value="{{ $header }}" id="y_{{ $loop->index }}">
                    <label class="form-check-label" for="y_{{ $loop->index }}">{{ $header }}</label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Simpan Dataset</button>
    </form>
</main>
@endsection
