<?php

use App\Http\Controllers\SoporteReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/reportes/ventas');

Route::redirect('/reportes/soporte', '/reportes/ventas');

Route::get('/reportes/ventas/export', [SoporteReportController::class, 'export'])
    ->name('reportes.ventas.export');

Route::get('/reportes/ventas', [SoporteReportController::class, 'index'])
    ->name('reportes.ventas');
