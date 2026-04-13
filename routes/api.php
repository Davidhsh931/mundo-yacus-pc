<?php

use Illuminate\Http\Request;
use App\Http\Controllers\GuineaPigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Rutas API sin autenticación para llamadas desde JavaScript
Route::get('/cuy/sugerir-stock/{id}', [GuineaPigController::class, 'sugerirStock'])->name('api.cuy.sugerir-stock');
Route::get('/sales/last-30-days', [GuineaPigController::class, 'getSalesLast30Days'])->name('api.sales.last-30-days');
