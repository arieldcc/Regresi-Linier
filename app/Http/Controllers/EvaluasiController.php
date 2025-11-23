<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class EvaluasiController extends Controller
{
    public function index()
    {
        $datasets = Dataset::all();
        return view('evaluasi.index', compact('datasets'));
    }

    public function hitung(Request $request)
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
            return back()->withErrors(['Dataset tidak memiliki baris data.']);
        }

        $headers = array_map('trim', $data[0]);
        $rows = array_slice($data, 1);

        // variabel dinamis
        $x_vars = $this->parseVars($dataset->x_variable);
        $y_var  = $this->parseSingleVar($dataset->y_variable);

        if (count($x_vars) < 1 || !$y_var) {
            return back()->withErrors(['Variabel X/Y belum lengkap di dataset.']);
        }

        // index kolom X dan Y
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

        // validasi numerik minimal
        [$cleanRows, $valErrors] = $this->validateNumericDataset($rows, $x_indexes, $y_index);

        if (count($cleanRows) === 0) {
            return back()->withErrors([
                'Semua baris tidak valid untuk evaluasi.',
                ...array_slice($valErrors, 0, 10)
            ]);
        }

        $p = count($x_indexes);
        if (count($cleanRows) <= $p) {
            return back()->withErrors([
                "Data valid terlalu sedikit. Dibutuhkan n > p (n=" . count($cleanRows) . ", p={$p})."
            ]);
        }

        // bangun X dan Y penuh (setelah cleaning)
        $X_all = [];
        $Y_all = [];
        foreach ($cleanRows as $row) {
            $X_all[] = array_map(fn($i) => (float)$row[$i], $x_indexes);
            $Y_all[] = (float)$row[$y_index];
        }

        // -------- SPLIT 80:20 --------
        $nAll = count($X_all);
        $nTrain = (int) floor(0.8 * $nAll);

        // optional: shuffle supaya uji lebih fair
        $idxs = range(0, $nAll - 1);
        shuffle($idxs);

        $trainIdx = array_slice($idxs, 0, $nTrain);
        $testIdx  = array_slice($idxs, $nTrain);

        $X_train = [];
        $Y_train = [];
        foreach ($trainIdx as $i) {
            $X_train[] = $X_all[$i];
            $Y_train[] = $Y_all[$i];
        }

        $X_test = [];
        $Y_test = [];
        foreach ($testIdx as $i) {
            $X_test[] = $X_all[$i];
            $Y_test[] = $Y_all[$i];
        }

        // latih model hanya dari data train
        $results = $this->linearRegression($X_train, $Y_train);

        $intercept = $results['intercept'];
        $coeffs = $results['coefficients'];

        // evaluasi train
        [$predTrain, $mapeTrain] = $this->evaluateMAPE($X_train, $Y_train, $intercept, $coeffs);

        // evaluasi test
        [$predTest, $mapeTest] = $this->evaluateMAPE($X_test, $Y_test, $intercept, $coeffs);

        return view('evaluasi.hasil', compact(
            'dataset',
            'x_vars',
            'y_var',
            'results',
            'predTrain',
            'predTest',
            'mapeTrain',
            'mapeTest'
        ));
    }

    /**
     * Hitung tabel prediksi + APE + MAPE untuk given X,Y dan model.
     * Return: [predTable, mape]
    */
    private function evaluateMAPE(array $X, array $Y, float $intercept, array $coeffs): array
    {
        $predTable = [];
        $apeSum = 0;
        $n = count($X);

        for ($i = 0; $i < $n; $i++) {
            $yHat = $intercept;
            foreach ($coeffs as $k => $b) {
                $yHat += $b * $X[$i][$k];
            }

            $yTrue = $Y[$i];

            $ape = null;
            if ($yTrue != 0) {
                $ape = abs(($yTrue - $yHat) / $yTrue) * 100;
                $apeSum += $ape;
            }

            $predTable[] = [
                'x' => $X[$i],
                'y' => $yTrue,
                'yhat' => $yHat,
                'ape' => $ape
            ];
        }

        $mape = ($n > 0) ? $apeSum / $n : null;

        return [$predTable, $mape];
    }

    /* ================== helper yang sama seperti PrediksiController ================== */

    private function linearRegression(array $X, array $Y)
    {
        $X_matrix = [];
        foreach ($X as $row) {
            $X_matrix[] = array_merge([1], $row);
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

        for ($i=0; $i<$n; $i++) {
            for ($j=0; $j<$n; $j++) {
                $identity[$i][$j] = ($i===$j) ? 1 : 0;
            }
        }

        for ($i=0; $i<$n; $i++) {
            $matrix[$i] = array_merge($matrix[$i], $identity[$i]);
        }

        for ($i=0; $i<$n; $i++) {
            $pivot = $matrix[$i][$i];
            if (abs($pivot) < 1e-12) {
                throw new \Exception("Matrix singular (XTX tidak invertible).");
            }

            for ($j=0; $j<2*$n; $j++) $matrix[$i][$j] /= $pivot;

            for ($k=0; $k<$n; $k++) {
                if ($k==$i) continue;
                $factor = $matrix[$k][$i];
                for ($j=0; $j<2*$n; $j++) {
                    $matrix[$k][$j] -= $factor * $matrix[$i][$j];
                }
            }
        }

        $inverse = [];
        for ($i=0; $i<$n; $i++) {
            $inverse[$i] = array_slice($matrix[$i], $n);
        }
        return $inverse;
    }

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

            foreach ($x_indexes as $xi => $colIndex) {
                $val = $row[$colIndex] ?? null;
                if ($val === null || $val === '') {
                    $rowErrors[] = "Baris " . ($ri+2) . ": X" . ($xi+1) . " kosong";
                } elseif (!is_numeric($val)) {
                    $rowErrors[] = "Baris " . ($ri+2) . ": X" . ($xi+1) . " non-numerik ('{$val}')";
                }
            }

            $yVal = $row[$y_index] ?? null;
            if ($yVal === null || $yVal === '') {
                $rowErrors[] = "Baris " . ($ri+2) . ": Y kosong";
            } elseif (!is_numeric($yVal)) {
                $rowErrors[] = "Baris " . ($ri+2) . ": Y non-numerik ('{$yVal}')";
            }

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                continue;
            }

            $cleanRows[] = $row;
        }

        return [$cleanRows, $errors];
    }
}
