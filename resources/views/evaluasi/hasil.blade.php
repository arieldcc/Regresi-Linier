@extends('layouts.admin.main')
@section('title','Hasil Evaluasi')

@section('content')
<main class="content-wrapper container">
  <h4>Hasil Evaluasi - {{ $dataset->name }}</h4>

  {{-- ================= RINGKASAN HASIL ================= --}}
  <div class="card p-3 my-3">
    <p><strong>Persamaan (dilatih dari 80% data):</strong></p>
    <p class="fs-5">{{ $results['equation'] }}</p>

    <p class="mt-2"><strong>MAPE Data Latih (Train 80%):</strong> {{ number_format($mapeTrain,2) }}%</p>
    <p><strong>MAPE Data Uji (Test 20%):</strong> {{ number_format($mapeTest,2) }}%</p>

    <p class="text-muted mb-0">
      MAPE sebagai metrik evaluasi error prediksi.
    </p>
  </div>

  {{-- ================= PENJABARAN SESUAI PROPOSAL ================= --}}
  <div class="card p-3 my-3">
    <h5>Penjabaran Output</h5>

    {{-- 1. Rumus regresi --}}
    <p><strong>1) Rumus yang digunakan (Regresi Linear Berganda)</strong></p>
    <p>
      Bentuk umum regresi linear berganda:
      <br>
      <span class="fs-6"><strong>Ŷ = a + b₁X₁ + b₂X₂ + ... + bₚXₚ</strong></span>
    </p>

    {{-- 2. Mapping variabel dinamis --}}
    <p class="mt-3"><strong>2) Variabel pada dataset ini</strong></p>
    <ul>
      @foreach($x_vars as $k => $x)
        <li><strong>X{{ $k }}</strong> = {{ $x }}</li>
      @endforeach
      <li><strong>Y</strong> = {{ $y_var }}</li>
    </ul>

    {{-- 3. Koefisien hasil training --}}
    <p class="mt-3"><strong>3) Koefisien hasil pelatihan (β)</strong></p>
    <ul>
      <li><strong>a (konstanta/intercept)</strong> = {{ $results['intercept'] }}</li>
      @foreach($results['coefficients'] as $k => $b)
        <li><strong>b{{ $k+1 }}</strong> untuk X{{ $k }} ({{ $x_vars[$k] }}) = {{ $b }}</li>
      @endforeach
    </ul>

    {{-- 4. Arti koefisien --}}
    <p class="mt-3"><strong>4) Makna koefisien</strong></p>
    <p>
      - Konstanta <strong>a</strong> adalah nilai dasar Ŷ ketika semua X = 0 (komponen matematis model). <br>
      @foreach($results['coefficients'] as $k => $b)
        - Jika <strong>{{ $x_vars[$k] }}</strong> naik 1 satuan dan variabel X lain tetap,
        maka Ŷ berubah sebesar <strong>{{ number_format($b,6) }}</strong> satuan.
        @if($b >= 0)
          (arahnya naik).
        @else
          (arahnya turun).
        @endif
        <br>
      @endforeach
    </p>

    {{-- 5. Rumus evaluasi MAPE --}}
    <p class="mt-3"><strong>5) Rumus evaluasi (MAPE)</strong></p>
    <p>
      Untuk setiap baris ke-i dihitung APE:
      <br>
      <span class="fs-6"><strong>
        APEᵢ = |(Yᵢ − Ŷᵢ) / Yᵢ| × 100%
      </strong></span>
    </p>
    <p>
      Lalu MAPE adalah rata-rata APE:
      <br>
      <span class="fs-6"><strong>
        MAPE = (1/n) × Σ APEᵢ
      </strong></span>
    </p>

    {{-- 6. Split 80:20 --}}
    <p class="mt-3"><strong>6) Dari mana MAPE Train dan MAPE Test berasal?</strong></p>
    <p>
      Dataset dibagi menjadi:
      <ul>
        <li><strong>80% data Train</strong>: dipakai melatih model → menghasilkan persamaan regresi.</li>
        <li><strong>20% data Test</strong>: tidak ikut melatih model → dipakai menguji kemampuan generalisasi.</li>
      </ul>
      Setelah model terbentuk dari Train, model dipakai memprediksi Train dan Test, lalu dihitung MAPE masing-masing:
      <br>
      - <strong>MAPE Train = {{ number_format($mapeTrain,2) }}%</strong> (rata-rata error pada 80% data latih) <br>
      - <strong>MAPE Test = {{ number_format($mapeTest,2) }}%</strong> (rata-rata error pada 20% data uji)
    </p>

    {{-- 7. Contoh hitung APE satu baris (opsional, biar konkret) --}}
    @php
      $exTrain = $predTrain[0] ?? null;
      $exTest  = $predTest[0] ?? null;
    @endphp

    @if($exTrain)
      <p class="mt-3"><strong>7) Contoh perhitungan APE (1 baris Train)</strong></p>
      <p>
        Misal 1 baris Train: <br>
        Aktual Y = <strong>{{ $exTrain['y'] }}</strong> <br>
        Prediksi Ŷ = <strong>{{ $exTrain['yhat'] }}</strong> <br><br>
        Maka:
        <br>
        APE = |({{ $exTrain['y'] }} − {{ $exTrain['yhat'] }}) / {{ $exTrain['y'] }}| × 100%
        = <strong>{{ number_format($exTrain['ape'],4) }}%</strong>
      </p>
    @endif

    @if($exTest)
      <p class="mt-3"><strong>8) Contoh perhitungan APE (1 baris Test)</strong></p>
      <p>
        Misal 1 baris Test: <br>
        Aktual Y = <strong>{{ $exTest['y'] }}</strong> <br>
        Prediksi Ŷ = <strong>{{ $exTest['yhat'] }}</strong> <br><br>
        Maka:
        <br>
        APE = |({{ $exTest['y'] }} − {{ $exTest['yhat'] }}) / {{ $exTest['y'] }}| × 100%
        = <strong>{{ number_format($exTest['ape'],4) }}%</strong>
      </p>
    @endif
  </div>

  {{-- ===================== CHARTS TRAIN ===================== --}}
  <h5 class="mt-4">Visualisasi Data Latih (Train)</h5>
  <div class="row">
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Aktual vs Prediksi (Train)</h6>
        <canvas id="chartTrainActual"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h6>APE per Baris (Train)</h6>
        <canvas id="chartTrainAPE"></canvas>
      </div>
    </div>
  </div>

  <table class="table table-bordered mt-3">
    <thead>
      <tr>
        @foreach($x_vars as $x) <th>{{ $x }}</th> @endforeach
        <th>{{ $y_var }} Aktual</th>
        <th>Ŷ Prediksi</th>
        <th>APE (%)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($predTrain as $row)
        <tr>
          @foreach($row['x'] as $val) <td>{{ $val }}</td> @endforeach
          <td>{{ $row['y'] }}</td>
          <td>{{ $row['yhat'] }}</td>
          <td>{{ $row['ape'] !== null ? number_format($row['ape'],2) : '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- ===================== CHARTS TEST ===================== --}}
  <h5 class="mt-5">Visualisasi Data Uji (Test)</h5>
  <div class="row">
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Aktual vs Prediksi (Test)</h6>
        <canvas id="chartTestActual"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h6>APE per Baris (Test)</h6>
        <canvas id="chartTestAPE"></canvas>
      </div>
    </div>
  </div>

  <table class="table table-bordered mt-3">
    <thead>
      <tr>
        @foreach($x_vars as $x) <th>{{ $x }}</th> @endforeach
        <th>{{ $y_var }} Aktual</th>
        <th>Ŷ Prediksi</th>
        <th>APE (%)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($predTest as $row)
        <tr>
          @foreach($row['x'] as $val) <td>{{ $val }}</td> @endforeach
          <td>{{ $row['y'] }}</td>
          <td>{{ $row['yhat'] }}</td>
          <td>{{ $row['ape'] !== null ? number_format($row['ape'],2) : '-' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <a href="{{ route('evaluasi.index') }}" class="btn btn-secondary mt-4">Kembali</a>
</main>
@endsection

@section('custom_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const trainLabels = @json(array_keys($predTrain));
  const trainActual = @json(array_map(fn($r) => $r['y'], $predTrain));
  const trainPred   = @json(array_map(fn($r) => $r['yhat'], $predTrain));
  const trainAPE    = @json(array_map(fn($r) => $r['ape'] ?? 0, $predTrain));

  const testLabels = @json(array_keys($predTest));
  const testActual = @json(array_map(fn($r) => $r['y'], $predTest));
  const testPred   = @json(array_map(fn($r) => $r['yhat'], $predTest));
  const testAPE    = @json(array_map(fn($r) => $r['ape'] ?? 0, $predTest));

  new Chart(document.getElementById('chartTrainActual'), {
    type: 'line',
    data: { labels: trainLabels, datasets: [
      { label: 'Aktual', data: trainActual, tension: 0.2 },
      { label: 'Prediksi', data: trainPred, tension: 0.2 }
    ]}
  });

  new Chart(document.getElementById('chartTrainAPE'), {
    type: 'bar',
    data: { labels: trainLabels, datasets: [
      { label: 'APE (%)', data: trainAPE }
    ]}
  });

  new Chart(document.getElementById('chartTestActual'), {
    type: 'line',
    data: { labels: testLabels, datasets: [
      { label: 'Aktual', data: testActual, tension: 0.2 },
      { label: 'Prediksi', data: testPred, tension: 0.2 }
    ]}
  });

  new Chart(document.getElementById('chartTestAPE'), {
    type: 'bar',
    data: { labels: testLabels, datasets: [
      { label: 'APE (%)', data: testAPE }
    ]}
  });
</script>
@endsection
