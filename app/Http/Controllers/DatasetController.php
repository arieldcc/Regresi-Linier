<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DatasetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datasets = Dataset::all();
        return view('datasets.index', compact('datasets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('datasets.upload');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string',
            'dataset_file' => 'required|file|mimes:xlsx,xls',
        ]);

        // Simpan file ke storage/app/public/uploads/tesis
        $path = $request->file('dataset_file')->store('uploads/dataset', 'public');

        // Pastikan file berhasil disimpan
        if (!Storage::disk('public')->exists($path)) {
            return back()->withErrors(['File gagal disimpan ke storage.']);
        }

        // Dapatkan path absolut untuk membaca dengan PhpSpreadsheet
        $absolutePath = Storage::disk('public')->path($path);

        // Baca file dan ambil header
        try {
            $spreadsheet = IOFactory::load($absolutePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];
        } catch (\Exception $e) {
            return back()->withErrors(['Gagal membaca file Excel: ' . $e->getMessage()]);
        }

        // Tampilkan form pemilihan variabel X dan Y
        return view('datasets.select_variables', [
            'file_path'   => $path, // path relatif di storage/public
            'headers'     => $headers,
            'name'        => $request->name,
            'description' => $request->description,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Dataset $dataset)
    {
        $spreadsheet = IOFactory::load(storage_path("app/public/{$dataset->file_path}"));
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray(null, true, true, true);
        $headers = array_shift($rows); // ambil header
        $data = array_values($rows);   // reset index
        $headers = array_values($headers);

        return view('datasets.view', [
            'dataset' => $dataset,
            'headers' => $headers,
            'rows' => $data,
            'xVars' => json_decode($dataset->x_variable, true),
            'yVars' => json_decode($dataset->y_variable, true),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dataset $dataset)
    {
        // Baca file excel dan ambil header
        $spreadsheet = IOFactory::load(storage_path('app/public/' . $dataset->file_path));
        $worksheet = $spreadsheet->getActiveSheet();
        $headers = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];

        return view('datasets.edit', compact('dataset', 'headers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dataset $dataset)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'x_variable' => 'required|array',
            'y_variable' => 'required|array',
            'dataset_file' => 'nullable|file|mimes:xlsx,xls',
        ]);

        // Update file jika ada
        if ($request->hasFile('dataset_file')) {
            // Hapus file lama
            if (Storage::disk('public')->exists($dataset->file_path)) {
                Storage::disk('public')->delete($dataset->file_path);
            }

            $file = $request->file('dataset_file');
            $storedPath = $file->store('datasets', 'public');
            $dataset->file_path = $storedPath;
        }

        $dataset->update([
            'name' => $request->name,
            'description' => $request->description,
            'x_variable' => json_encode($request->x_variable),
            'y_variable' => json_encode($request->y_variable),
        ]);

        return redirect()->route('dataset.index')->with('status', 'Dataset berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Hapus file dari storage
        if (Storage::disk('public')->exists($dataset->file_path)) {
            Storage::disk('public')->delete($dataset->file_path);
        }

        $dataset->delete();

        return redirect()->route('dataset.index')->with('status', 'Dataset berhasil dihapus.');
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'file_path' => 'required|string',
            'x_variable' => 'required|array|min:1',
            'y_variable' => 'required|array|min:1',
        ]);

        Dataset::create([
            'name' => $request->name,
            'description' => $request->description,
            'file_path' => $request->file_path,
            'x_variable' => json_encode($request->x_variable),
            'y_variable' => json_encode($request->y_variable),
        ]);

        return redirect()->route('dataset.index')->with('status', 'Dataset berhasil disimpan.');
    }

    public function updateExcel(Request $request, Dataset $dataset)
    {
        // Ambil data dari form, default [] biar tidak error kalau kosong
        $data = $request->input('data', []);

        // Reindex baris ke 0,1,2,...
        $data = array_values($data);

        $path = Storage::disk('public')->path($dataset->file_path);

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($data as $i => $row) {
            // Reindex kolom di setiap baris ke 0,1,2,...
            $row = array_values($row);

            foreach ($row as $j => $value) {
                // Sekarang $j pasti integer
                $colLetter = Coordinate::stringFromColumnIndex($j + 1);
                // Baris data pertama ada di row 2 (karena row 1 header)
                $rowNumber = $i + 2;

                $sheet->setCellValue("{$colLetter}{$rowNumber}", $value);
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);

        return back()->with('status', 'Dataset berhasil diperbarui.');
    }
}
