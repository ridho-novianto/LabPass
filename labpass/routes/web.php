<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerifikasiMahasiswaController;
use App\Http\Controllers\Admin\AdminDashboardController;

// 1. Portal Mahasiswa (Tanpa Login)
Route::get('/', [VerifikasiMahasiswaController::class, 'index'])->name('home');
Route::get('/verifikasi', [VerifikasiMahasiswaController::class, 'index'])->name('verifikasi.index');
Route::post('/verifikasi/scan', [VerifikasiMahasiswaController::class, 'scan'])->name('verifikasi.scan');
Route::get('/verifikasi/status/{token}', [VerifikasiMahasiswaController::class, 'status'])->name('verifikasi.status');
Route::get('/verifikasi/status-ajax/{token}', [VerifikasiMahasiswaController::class, 'checkStatusAjax'])->name('verifikasi.status-ajax');
Route::post('/verifikasi/pilih-materi/{token}', [VerifikasiMahasiswaController::class, 'pilihMateri'])->name('verifikasi.pilih-materi');

// Halaman Sesi & Praktikum Mahasiswa
Route::get('/praktikum/{token}', [VerifikasiMahasiswaController::class, 'praktikum'])->name('praktikum.show');
Route::get('/praktikum/{token}/ai-summary', [VerifikasiMahasiswaController::class, 'getAiSummary'])->name('praktikum.ai-summary');
Route::post('/praktikum/{token}/checkout', [VerifikasiMahasiswaController::class, 'checkout'])->name('praktikum.checkout');

// 2. Autentikasi Dosen & Aslab
Route::get('/login', [AdminDashboardController::class, 'loginForm'])->name('login');
Route::post('/login', [AdminDashboardController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
Route::get('/logout', [AdminDashboardController::class, 'logout'])->name('logout.get');

// 3. Dashboard Admin (Wajib Login)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/kehadiran/{id}/approve', [AdminDashboardController::class, 'approveKehadiran'])->name('kehadiran.approve');
    Route::post('/kehadiran/{id}/tolak', [AdminDashboardController::class, 'tolakKehadiran'])->name('kehadiran.tolak');
    Route::post('/sesi', [AdminDashboardController::class, 'storeSesi'])->name('sesi.store');
    
    // Kelola & Upload Dokumen Materi Dosen
    Route::post('/materi', [AdminDashboardController::class, 'storeMateri'])->name('materi.store');
    Route::post('/materi/{id}/upload-file', [AdminDashboardController::class, 'uploadFileMateri'])->name('materi.upload-file');
    Route::delete('/materi/{id}', [AdminDashboardController::class, 'destroyMateri'])->name('materi.destroy');
    Route::get('/materi/{id}/ai-preview', [AdminDashboardController::class, 'previewAiSummary'])->name('materi.ai-preview');
});
