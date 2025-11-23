@extends('layouts.admin.main')
@section('title','Evaluasi')

@section('content')
<main class="content-wrapper container">
  <h4>Evaluasi Model (MAPE)</h4>

  <form action="{{ route('evaluasi.hitung') }}" method="POST" class="mt-3">
    @csrf
    <div class="mb-3">
      <label class="form-label">Pilih Dataset</label>
      <select name="dataset_id" class="form-select" required>
        <option value="">-- pilih --</option>
        @foreach($datasets as $ds)
          <option value="{{ $ds->id }}">{{ $ds->name }}</option>
        @endforeach
      </select>
    </div>
    <button class="btn btn-primary">Hitung Evaluasi</button>
  </form>
</main>
@endsection
