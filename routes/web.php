<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

Route::get('/dashboard', function () {
    return view('welcome');
})->middleware('auth');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn() => view('admin.dashboard'));
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('roles', RoleController::class);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('permissions', PermissionController::class);
    Route::get('role-permissions/{role}', [PermissionController::class, 'editRolePermissions'])->name('role.permissions.edit');
    Route::post('role-permissions/{role}', [PermissionController::class, 'updateRolePermissions'])->name('role.permissions.update');
});

Route::middleware(['auth', 'role:admin,pegawai,user'])->group(function () {
    Route::get('dataset', [DatasetController::class, 'index'])->name('dataset.index');
    Route::get('dataset/create', [DatasetController::class, 'create'])->name('dataset.create');
    Route::post('dataset', [DatasetController::class, 'store'])->name('dataset.store');
    Route::post('dataset/finalize', [DatasetController::class, 'finalize'])->name('dataset.finalize');
    Route::delete('dataset/{id}', [DatasetController::class, 'destroy'])->name('dataset.destroy');
    Route::get('dataset/{dataset}/edit', [DatasetController::class, 'edit'])->name('dataset.edit');
    Route::put('dataset/{dataset}', [DatasetController::class, 'update'])->name('dataset.update');
    Route::get('dataset/{dataset}/show', [DatasetController::class, 'show'])->name('dataset.show');
    Route::put('datasets/update-excel/{dataset}', [DatasetController::class, 'updateExcel'])->name('dataset.updateExcel');
});

Route::middleware(['auth'])->group(function () {
    Route::get('prediksi', [PrediksiController::class, 'index'])->name('prediksi.index');
    Route::post('prediksi/train', [PrediksiController::class, 'train'])->name('prediksi.train');
    Route::post('prediksi/test', [PrediksiController::class, 'test'])->name('prediksi.test');

    Route::get('prediksi/simulasi/{dataset}', [PrediksiController::class, 'simulasi'])
        ->name('prediksi.simulasi');
});

Route::middleware(['auth'])->group(function () {
    Route::get('evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::post('evaluasi/hitung', [EvaluasiController::class, 'hitung'])->name('evaluasi.hitung');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('halaman', PageController::class)->names([
        'index' => 'halaman.index',
    ]);
});


Route::get('{any}', function ($any) {
    return view('errors.under_construction', ['menu' => $any]);
})->where('any', '.*')->middleware('auth');
