<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\PipeDataController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PipeMasterController;
use App\Http\Controllers\PipeCounterController;

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Protected App Routes (Auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/gudang/{id}', [WarehouseController::class, 'show'])->name('warehouse.show');

    // Opname
    Route::get('/opname/{warehouse}/{block}', [StockOpnameController::class, 'create'])->name('opname.create');
    Route::post('/opname', [StockOpnameController::class, 'store'])->name('opname.store');
    Route::get('/opname/{id}/edit', [StockOpnameController::class, 'edit'])->name('opname.edit');
    Route::put('/opname/{id}', [StockOpnameController::class, 'update'])->name('opname.update');
    Route::delete('/opname/{id}', [StockOpnameController::class, 'destroy'])->name('opname.destroy');

    // Laporan
    Route::get('/laporan', [StockOpnameController::class, 'report'])->name('report.index');
    Route::get('/laporan/export', [StockOpnameController::class, 'export'])->name('report.export');

    // Master Data Spesifikasi Pipa
    Route::get('/master', [PipeMasterController::class, 'index'])->name('master.index');

    Route::post('/master/size', [PipeMasterController::class, 'storeSize'])->name('master.size.store');
    Route::delete('/master/size/{id}', [PipeMasterController::class, 'destroySize'])->name('master.size.destroy');

    Route::post('/master/grade', [PipeMasterController::class, 'storeGrade'])->name('master.grade.store');
    Route::delete('/master/grade/{id}', [PipeMasterController::class, 'destroyGrade'])->name('master.grade.destroy');

    Route::post('/master/class', [PipeMasterController::class, 'storeClass'])->name('master.class.store');
    Route::delete('/master/class/{id}', [PipeMasterController::class, 'destroyClass'])->name('master.class.destroy');

    Route::post('/master/category', [PipeMasterController::class, 'storeCategory'])->name('master.category.store');
    Route::delete('/master/category/{id}', [PipeMasterController::class, 'destroyCategory'])->name('master.category.destroy');

    // API endpoint for calculations
    Route::get('/api/pipe-info/{size}', [PipeDataController::class, 'getInfo'])->name('api.pipe-info');

    // AI Pipe Counter
    Route::post('/api/count-pipes', [PipeCounterController::class, 'count'])->name('api.count-pipes');
});
