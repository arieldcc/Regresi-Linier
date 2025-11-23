@extends('layouts.admin.main')
@section('title', 'Simulasi Regresi')
@section('custom_css')
    <style>
        /* pastikan card tidak lebih lebar dari container */
        .sim-card {
            max-width: 100%;
            overflow: hidden;
        }

        /* wrapper untuk scroll horizontal */
        .matrix-wrap {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
        }

        table.matrix {
            border-collapse: collapse;
            width: max-content;  /* biar tabel ikut konten, lalu di-scroll */
            min-width: 100%;     /* tapi tetap minimal selebar container */
            font-size: 0.9rem;
            white-space: nowrap; /* angka tidak turun baris */
        }

        table.matrix th, table.matrix td {
            border: 1px solid #eee;
            padding: 6px 10px;
            text-align: right;
        }

        table.matrix th:first-child, table.matrix td:first-child {
            text-align: center;
            position: sticky;     /* biar nomor baris tetap keliatan saat scroll */
            left: 0;
            background: #fafafa;
            z-index: 2;
            min-width: 44px;
        }

        /* rapikan judul step */
        .step-title {
            font-weight: 600;
            margin-bottom: 6px;
        }

        .step-desc {
            color: #6b7280;
            margin-bottom: 8px;
        }
        /* table.matrix { border-collapse: collapse; width: 100%; margin-top:8px; }
        table.matrix th, table.matrix td { border:1px solid #ddd; padding:6px 8px; text-align:right; }
        table.matrix th:first-child, table.matrix td:first-child { text-align:center; }
        .muted { color:#666; } */
    </style>
@endsection
@section('content')
<main class="content-wrapper container">

  <h4>Simulasi Regresi Linear Berganda (Interaktif)</h4>
  <p class="text-muted">
    Dataset: <strong>{{ $dataset->name }}</strong>.
    Halaman ini menampilkan pembentukan model step-by-step sampai persamaan regresi terbentuk.
  </p>

  {{-- Data dari backend ke JS --}}
  <script>
    window.SIM_DATA = {
      headers: @json($headers),
      rows: @json($rows),
      x_vars: @json($x_vars),
      y_var: @json($y_var),
    };
  </script>

  <div class="card p-3 mt-3">
    <h5>1) Pilih variabel X dan Y</h5>
    <div class="row">
      <div class="col-md-6">
        <label><strong>X (boleh lebih dari satu)</strong></label>
        <select id="xSelect" multiple size="6" class="form-select"></select>
      </div>
      <div class="col-md-6">
        <label><strong>Y (target)</strong></label>
        <select id="ySelect" size="6" class="form-select"></select>
      </div>
    </div>
    <button id="runBtn" class="btn btn-primary mt-3">Jalankan Simulasi</button>
    <span id="runStatus" class="ms-2 text-muted"></span>
  </div>

  <div id="steps" style="display:none;">
    <h5 class="mt-4">2) Proses Perhitungan (Step by Step)</h5>

    <div id="step0" class="card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step1" class="card sim-card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step2" class="card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step3" class="card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step4" class="card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step5" class="card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step6" class="card p-3 mt-2 border-start border-4 border-primary"></div>
    <div id="step7" class="card p-3 mt-2 border-start border-4 border-success"></div>
  </div>
</main>
@endsection

@section('custom_js')
<script>
  const { headers, rows, x_vars, y_var } = window.SIM_DATA;

  const xSelect = document.getElementById('xSelect');
  const ySelect = document.getElementById('ySelect');
  const runBtn = document.getElementById('runBtn');
  const runStatus = document.getElementById('runStatus');
  const steps = document.getElementById('steps');

  function toNumber(x){
    const n = Number(x);
    return Number.isFinite(n) ? n : 0;
  }

  function fillSelectors(){
    xSelect.innerHTML = '';
    ySelect.innerHTML = '';

    headers.forEach((h, idx)=>{
      const ox = document.createElement('option');
      ox.value = idx; ox.textContent = h;
      if (x_vars.includes(h)) ox.selected = true;
      xSelect.appendChild(ox);

      const oy = document.createElement('option');
      oy.value = idx; oy.textContent = h;
      if (y_var === h) oy.selected = true;
      ySelect.appendChild(oy);
    });
  }

  /* -------- Matrix helpers (normal equation) -------- */
  function transpose(A){
    const r=A.length, c=A[0].length;
    const T=Array.from({length:c},()=>Array(r).fill(0));
    for(let i=0;i<r;i++) for(let j=0;j<c;j++) T[j][i]=A[i][j];
    return T;
  }

  function multiply(A,B){
    const rA=A.length, cA=A[0].length, cB=B[0].length;
    const out=Array.from({length:rA},()=>Array(cB).fill(0));
    for(let i=0;i<rA;i++){
      for(let k=0;k<cA;k++){
        for(let j=0;j<cB;j++){
          out[i][j]+=A[i][k]*B[k][j];
        }
      }
    }
    return out;
  }

  function inverse(M){
    const n=M.length;
    const aug=M.map((row,i)=>{
      const Irow=Array(n).fill(0); Irow[i]=1;
      return row.slice().concat(Irow);
    });

    for(let i=0;i<n;i++){
      let pivot=aug[i][i];
      if(Math.abs(pivot)<1e-12){
        let swap=-1;
        for(let r=i+1;r<n;r++){
          if(Math.abs(aug[r][i])>1e-12){ swap=r; break; }
        }
        if(swap===-1) throw new Error('Matriks singular (XᵀX tidak invertible).');
        [aug[i],aug[swap]]=[aug[swap],aug[i]];
        pivot=aug[i][i];
      }
      for(let j=0;j<2*n;j++) aug[i][j]/=pivot;
      for(let r=0;r<n;r++){
        if(r===i) continue;
        const factor=aug[r][i];
        for(let j=0;j<2*n;j++){
          aug[r][j]-=factor*aug[i][j];
        }
      }
    }
    return aug.map(row=>row.slice(n));
  }

  function findNonNumeric(rows, xIdx, yIdx, maxShow=10){
    const bad = [];
    rows.forEach((r, ri)=>{
        xIdx.forEach((ci, k)=>{
        const v = r[ci];
        if (v !== null && v !== "" && isNaN(Number(v))) {
            bad.push(`Baris ${ri+2} kolom X${k+1} = '${v}'`);
        }
        });
        const vy = r[yIdx];
        if (vy !== null && vy !== "" && isNaN(Number(vy))) {
        bad.push(`Baris ${ri+2} kolom Y = '${vy}'`);
        }
    });
    return bad.slice(0, maxShow);
    }


  function formatMatrix(A, digits=4, maxRows=8){
        const shown = A.slice(0, maxRows);
        let html = `<div class="matrix-wrap"><table class="matrix"><tbody>`;

        shown.forEach((row,i)=>{
            html += `<tr><td>${i+1}</td>` +
            row.map(v => `<td>${Number(v).toFixed(digits)}</td>`).join('') +
            `</tr>`;
        });

        if (A.length > maxRows){
            html += `<tr>
            <td colspan="${A[0].length+1}" class="muted">
                ... ${A.length-maxRows} baris tidak ditampilkan
            </td>
            </tr>`;
        }

        html += `</tbody></table></div>`;
        return html;
    }


  runBtn.addEventListener('click', ()=>{
    try{
      const xIdx = Array.from(xSelect.selectedOptions).map(o=>Number(o.value));
      const yIdx = Number(ySelect.value);

      if(xIdx.length<1 || Number.isNaN(yIdx)){
        runStatus.textContent = 'Pilih minimal 1 X dan 1 Y.';
        return;
      }
      if(xIdx.includes(yIdx)){
        runStatus.textContent = 'Y tidak boleh masuk X.';
        return;
      }

      const bad = findNonNumeric(rows, xIdx, yIdx);

    if (bad.length > 0) {
        runStatus.textContent =
            "Data mengandung nilai non-numerik. Perbaiki dataset terlebih dahulu.";
        steps.style.display = "none";
        alert("Nilai non-numerik ditemukan:\n" + bad.join("\n"));
        return;
    }

      runStatus.textContent = 'Menghitung...';

      const xNames = xIdx.map(i=>headers[i]);
      const yName = headers[yIdx];

      const Xraw = rows.map(r => xIdx.map(i=>toNumber(r[i])));
      const Yraw = rows.map(r => toNumber(r[yIdx]));

      const X = Xraw.map(row=>[1,...row]);  // add intercept
      const Y = Yraw.map(y=>[y]);

      const XT = transpose(X);
      const XTX = multiply(XT, X);
      const XTY = multiply(XT, Y);
      const XTXinv = inverse(XTX);
      const betaMat = multiply(XTXinv, XTY);
      const beta = betaMat.map(r=>r[0]);

      steps.style.display='block';

      document.getElementById('step0').innerHTML=`
        <h6>Step 0. Definisi Variabel</h6>
        <p>X dipilih: <strong>${xNames.join(', ')}</strong></p>
        <p>Y dipilih: <strong>${yName}</strong></p>
        <p>n = ${X.length}, p = ${xIdx.length}</p>
      `;

      document.getElementById('step1').innerHTML=`
        <div class="step-title">Step 1. Matriks X (dengan intercept)</div>
        <div class="step-desc">X berukuran n × (p+1), kolom pertama bernilai 1.</div>
        ${formatMatrix(X)}
        `;

      document.getElementById('step2').innerHTML=`
        <h6>Step 2. Transpose Xᵀ</h6>
        ${formatMatrix(XT)}
      `;

      document.getElementById('step3').innerHTML=`
        <h6>Step 3. Hitung XᵀX</h6>
        ${formatMatrix(XTX)}
      `;

      document.getElementById('step4').innerHTML=`
        <h6>Step 4. Inverse (XᵀX)⁻¹</h6>
        ${formatMatrix(XTXinv)}
      `;

      document.getElementById('step5').innerHTML=`
        <h6>Step 5. Hitung XᵀY</h6>
        ${formatMatrix(XTY)}
      `;

      document.getElementById('step6').innerHTML=`
        <h6>Step 6. β = (XᵀX)⁻¹ XᵀY</h6>
        <p>β = [a, b₁, ..., bₚ]</p>
        ${formatMatrix(betaMat, 6)}
        <p><strong>β:</strong> [${beta.map(v=>v.toFixed(6)).join(', ')}]</p>
      `;

      const intercept = beta[0];
      const slopes = beta.slice(1);
      const eqTerms = slopes.map((b,i)=>`${b.toFixed(6)} × (${xNames[i]})`);
      const eq = `Ŷ = ${intercept.toFixed(6)} ${eqTerms.length ? ' + ' + eqTerms.join(' + ') : ''}`;

      document.getElementById('step7').innerHTML=`
        <h6>Step 7. Persamaan Regresi</h6>
        <p class="fs-5"><strong>${eq}</strong></p>
      `;

      runStatus.textContent='Selesai.';
    }catch(e){
      runStatus.textContent = e.message;
      steps.style.display='none';
    }
  });

  fillSelectors();
  </script>
@endsection
