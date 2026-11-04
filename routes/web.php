<?php

use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\InternalAuthController;
use App\Http\Middleware\DashboardAccess;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Explicitly isolated demonstration. No operational records or authentication bypass.
Route::get('/demo/{role}/{page?}', function (string $role, ?string $page = null) {
    abort_unless(app()->environment('local', 'testing'), 404);
    abort_unless(in_array($role, ['admin', 'verifikator'], true), 404);
    $allowed = $role === 'admin'
        ? ['dashboard', 'pengaduan', 'konten', 'jdih', 'basis-ai', 'anggaran', 'analitik', 'pencarian', 'akun', 'peran', 'aktivitas', 'pengaturan']
        : ['dashboard', 'pengaduan', 'aktivitas'];
    abort_unless(in_array($page ?? 'dashboard', $allowed, true), 403);

    return view('dashboard.index', ['role' => $role, 'demo' => true]);
})->name('dashboard.demo');

Route::view('/login', 'dashboard.login')->name('login');
Route::post('/login', [InternalAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
Route::post('/logout', [InternalAuthController::class, 'logout'])->name('logout');
Route::middleware(DashboardAccess::class)->group(function () {
    Route::get('/internal/data', [DashboardDataController::class, 'index']);
    Route::post('/internal/data/{collection}', [DashboardDataController::class, 'save']);
    Route::delete('/internal/data/{collection}/{key}', [DashboardDataController::class, 'destroy']);
    Route::get('/internal/files/{key}', [DashboardDataController::class, 'download']);
    Route::get('/{role}/{page?}', function (string $role, ?string $page = null) {
        abort_unless(in_array($page ?? 'dashboard', ['dashboard', 'pengaduan', 'konten', 'jdih', 'basis-ai', 'anggaran', 'analitik', 'pencarian', 'akun', 'peran', 'aktivitas', 'pengaturan'], true), 404);

        return view('dashboard.index', ['role' => $role, 'demo' => false]);
    })->where('role', 'admin|verifikator');
});
