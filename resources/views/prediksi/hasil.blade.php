@extends('layouts.admin.main')
@section('title', 'Hasil Prediksi')
@section('content')
<main class="content-wrapper container">
    <h4>Persamaan Regresi</h4>
    <p>{{ $results['equation'] }}</p>

    <h5>Uji Model (Input Data Baru)</h5>
    <form id="ujiForm">
        @csrf

        @foreach ($x_vars as $i => $var)
            <label class="mt-2">{{ $var }}</label>
            <input type="number" name="x[{{ $i }}]" step="any" class="form-control" required>
        @endforeach

        <input type="hidden" name="coefficients" value="{{ json_encode($results['coefficients']) }}">
        <input type="hidden" name="intercept" value="{{ $results['intercept'] }}">

        <button type="submit" class="btn btn-success mt-3">Uji Prediksi</button>
    </form>

    <div id="hasilPrediksi" class="mt-3"></div>

    <script>
    document.getElementById('ujiForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        const res = await fetch("{{ route('prediksi.test') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        });

        const data = await res.json();

        if (!res.ok) {
            document.getElementById('hasilPrediksi').innerHTML =
                `<div class="alert alert-danger">${data.error ?? 'Terjadi error'}</div>`;
            return;
        }

        document.getElementById('hasilPrediksi').innerHTML =
            `<div class="alert alert-info">Hasil Prediksi Y: <strong>${data.prediksi}</strong></div>`;
    });
    </script>
</main>
@endsection
