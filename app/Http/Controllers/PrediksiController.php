<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class PrediksiController extends Controller
{
    public function index()
    {
        $datasets = Dataset::all();
        return view('prediksi.index', compact('datasets'));
    }

    public function train(Request $request)
    {
        $dataset = Dataset::findOrFail($request->dataset_id);
        $path = Storage::disk('public')->path($dataset->file_path);

        if (!file_exists($path)) {
            return back()->withErrors(['File dataset tidak ditemukan.']);
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        if (count($data) < 2) {
            return back()->withErrors(['Dataset tidak memiliki baris data (hanya header atau kosong).']);
        }

        $headers = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);

        // 1) parse variabel dinamis
        $x_vars = $this->parseVars($dataset->x_variable);
        $y_var  = $this->parseSingleVar($dataset->y_variable);

        if (count($x_vars) < 1 || !$y_var) {
            return back()->withErrors(['Variabel X/Y belum lengkap di dataset.']);
        }

        // 2) cari index kolom X dan Y berdasarkan header excel
        $x_indexes = [];
        foreach ($x_vars as $x) {
            $idx = array_search($x, $headers);
            if ($idx === false) {
                return back()->withErrors(["Kolom X '{$x}' tidak ditemukan di Excel."]);
            }
            $x_indexes[] = $idx;
        }

        $y_index = array_search($y_var, $headers);
        if ($y_index === false) {
            return back()->withErrors(["Kolom Y '{$y_var}' tidak ditemukan di Excel."]);
        }

        // 3) validasi numerik (data cleaning minimal)
        [$cleanRows, $valErrors] = $this->validateNumericDataset($rows, $x_indexes, $y_index);

        if (count($cleanRows) === 0) {
            return back()->withErrors([
                'Semua baris tidak valid untuk pelatihan.',
                ...array_slice($valErrors, 0, 10)
            ]);
        }

        // syarat minimal agar regresi bisa dihitung stabil:
        // n harus > p (jumlah data lebih banyak dari variabel)
        $p = count($x_indexes);
        if (count($cleanRows) <= $p) {
            return back()->withErrors([
                "Data valid terlalu sedikit. Dibutuhkan minimal n > p (n=" . count($cleanRows) . ", p={$p}).",
                ...array_slice($valErrors, 0, 10)
            ]);
        }

        // kalau ada error, tampilkan sebagai warning tapi tetap lanjut latih
        // (alternatif: stop total. tapi ini lebih user-friendly)
        if (!empty($valErrors)) {
            session()->flash('warning', array_slice($valErrors, 0, 10));
        }

        // 4) bangun matriks X dan Y dari baris bersih
        $X = [];
        $Y = [];

        foreach ($cleanRows as $row) {
            $X[] = array_map(fn($i) => (float) $row[$i], $x_indexes);
            $Y[] = (float) $row[$y_index];
        }

        // 5) hitung regresi
        try {
            $results = $this->linearRegression($X, $Y);
        } catch (\Throwable $e) {
            return back()->withErrors([
                "Gagal menghitung model regresi: " . $e->getMessage()
            ]);
        }

        return view('prediksi.hasil', compact('results', 'dataset', 'x_vars', 'y_var'));
    }

    public function test(Request $request)
    {
        // Laravel otomatis parse FormData → x jadi array
        $coefficients = json_decode($request->input('coefficients', '[]'), true);
        $intercept    = floatval($request->input('intercept', 0));
        $x_input      = array_map('floatval', $request->input('x', []));

        if (empty($coefficients) || empty($x_input)) {
            return response()->json(['error' => 'Data tidak lengkap'], 422);
        }

        if (count($coefficients) !== count($x_input)) {
            return response()->json(['error' => 'Jumlah variabel tidak cocok'], 422);
        }

        $y_pred = $intercept;
        foreach ($coefficients as $i => $b) {
            $y_pred += $b * $x_input[$i];
        }

        return response()->json(['prediksi' => $y_pred]);
    }

    private function linearRegression(array $X, array $Y)
    {
        $n = count($X);
        $p = count($X[0]);

        $X_matrix = [];
        foreach ($X as $row) {
            $X_matrix[] = array_merge([1], $row); // Add intercept
        }

        $X_transpose = array_map(null, ...$X_matrix);
        $XTX = $this->matrixMultiply($X_transpose, $X_matrix);
        $XTY = $this->matrixMultiply($X_transpose, array_map(fn($y) => [$y], $Y));
        $XTX_inv = $this->matrixInverse($XTX);
        $coeffs = $this->matrixMultiply($XTX_inv, $XTY);

        $intercept = $coeffs[0][0];
        $slopes = array_column(array_slice($coeffs, 1), 0);

        return [
            'intercept' => $intercept,
            'coefficients' => $slopes,
            'equation' => 'Y = ' . $intercept . ' + ' .
                implode(' + ', array_map(fn($b, $i) => "$b*X$i", $slopes, array_keys($slopes)))
        ];
    }

    private function matrixMultiply(array $A, array $B)
    {
        $result = [];

        $aRows = count($A);
        $aCols = count($A[0]);
        $bCols = count($B[0]);

        for ($i = 0; $i < $aRows; $i++) {
            for ($j = 0; $j < $bCols; $j++) {
                $sum = 0;
                for ($k = 0; $k < $aCols; $k++) {
                    $sum += $A[$i][$k] * $B[$k][$j];
                }
                $result[$i][$j] = $sum;
            }
        }

        return $result;
    }

    private function matrixInverse(array $matrix)
    {
        $n = count($matrix);
        $identity = [];

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $identity[$i][$j] = ($i === $j) ? 1 : 0;
            }
        }

        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = array_merge($matrix[$i], $identity[$i]);
        }

        for ($i = 0; $i < $n; $i++) {
            $pivot = $matrix[$i][$i];
            if (abs($pivot) < 1e-12) {
                throw new \Exception("Matrix singular (XTX tidak invertible).");
            }

            for ($j = 0; $j < 2 * $n; $j++) {
                $matrix[$i][$j] /= $pivot;
            }

            for ($k = 0; $k < $n; $k++) {
                if ($k != $i) {
                    $factor = $matrix[$k][$i];
                    for ($j = 0; $j < 2 * $n; $j++) {
                        $matrix[$k][$j] -= $factor * $matrix[$i][$j];
                    }
                }
            }
        }

        $inverse = [];
        for ($i = 0; $i < $n; $i++) {
            $inverse[$i] = array_slice($matrix[$i], $n);
        }

        return $inverse;
    }

    // ---------- parser variabel dinamis ----------
    private function parseVars($raw): array
    {
        if (is_array($raw)) return $raw;

        $raw = trim((string)$raw);
        if ($raw === '') return [];

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values($decoded);
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private function parseSingleVar($raw): ?string
    {
        if (is_array($raw)) return $raw[0] ?? null;

        $raw = trim((string)$raw);
        if ($raw === '') return null;

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_string($decoded)) return $decoded;
            if (is_array($decoded)) return $decoded[0] ?? null;
        }

        return $raw;
    }

    private function validateNumericDataset(array $rows, array $x_indexes, int $y_index): array
    {
        $errors = [];
        $cleanRows = [];

        foreach ($rows as $ri => $row) {
            $rowErrors = [];

            // cek semua X
            foreach ($x_indexes as $xi => $colIndex) {
                $val = $row[$colIndex] ?? null;

                if ($val === null || $val === '') {
                    $rowErrors[] = "Baris " . ($ri+2) . ": X" . ($xi+1) . " kosong";
                } elseif (!is_numeric($val)) {
                    $rowErrors[] = "Baris " . ($ri+2) . ": X" . ($xi+1) . " non-numerik ('{$val}')";
                }
            }

            // cek Y
            $yVal = $row[$y_index] ?? null;
            if ($yVal === null || $yVal === '') {
                $rowErrors[] = "Baris " . ($ri+2) . ": Y kosong";
            } elseif (!is_numeric($yVal)) {
                $rowErrors[] = "Baris " . ($ri+2) . ": Y non-numerik ('{$yVal}')";
            }

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                continue; // skip baris yang kotor
            }

            $cleanRows[] = $row; // baris valid
        }

        return [$cleanRows, $errors];
    }

    public function simulasi(Dataset $dataset)
    {
        $path = Storage::disk('public')->path($dataset->file_path);

        if (!file_exists($path)) {
            return back()->withErrors(['File dataset tidak ditemukan.']);
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        if (count($data) < 2) {
            return back()->withErrors(['Dataset tidak memiliki baris data (hanya header atau kosong).']);
        }

        $headers = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);

        // 1) variabel dinamis dari DB
        $x_vars = $this->parseVars($dataset->x_variable);
        $y_var  = $this->parseSingleVar($dataset->y_variable);

        if (count($x_vars) < 1 || !$y_var) {
            return back()->withErrors(['Variabel X/Y belum lengkap di dataset.']);
        }

        // 2) cari index kolom X dan Y
        $x_indexes = [];
        foreach ($x_vars as $x) {
            $idx = array_search($x, $headers);
            if ($idx === false) {
                return back()->withErrors(["Kolom X '{$x}' tidak ditemukan di Excel."]);
            }
            $x_indexes[] = $idx;
        }

        $y_index = array_search($y_var, $headers);
        if ($y_index === false) {
            return back()->withErrors(["Kolom Y '{$y_var}' tidak ditemukan di Excel."]);
        }

        // 3) validasi numerik untuk simulasi
        [$cleanRows, $valErrors] = $this->validateNumericDataset($rows, $x_indexes, $y_index);

        return view('prediksi.simulasi', [
            'dataset'   => $dataset,
            'headers'   => $headers,
            'rows'      => $rows,        // mentah (opsional display)
            'cleanRows' => $cleanRows,   // valid untuk perhitungan
            'x_vars'    => $x_vars,
            'y_var'     => $y_var,
            'valErrors' => $valErrors,
        ]);
    }

}
