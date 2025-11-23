@extends('layouts.admin.main')
@section('title', 'Prediksi')

@section('content')
<main class="content-wrapper container">
    <form action="{{ route('prediksi.train') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="dataset">Pilih Dataset:</label>
            <select id="datasetSelect" name="dataset_id" class="form-control" required>
                @foreach ($datasets as $ds)
                    <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary" type="submit">Latih Model</button>

        {{-- tombol simulasi sesuai pilihan dropdown --}}
        <a id="simulasiLink"
           href="#"
           class="btn btn-outline-secondary ms-2">
           Simulasi Step-by-Step
        </a>
    </form>
</main>

<script>
(function () {
    const select = document.getElementById('datasetSelect');
    const link = document.getElementById('simulasiLink');

    function updateLink() {
        const id = select.value;
        // base url dari route simulasi, lalu ganti trailing id
        const base = "{{ url('prediksi/simulasi') }}";
        link.href = `${base}/${id}`;
    }

    select.addEventListener('change', updateLink);
    updateLink(); // set awal
})();
</script>
@endsection
