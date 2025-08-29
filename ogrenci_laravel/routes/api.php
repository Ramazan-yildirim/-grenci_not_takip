<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OgrenciController;
use App\Http\Controllers\EgitmenController;
use App\Http\Controllers\DersController;
use App\Http\Controllers\NotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::apiResource('ogrenciler', OgrenciController::class)
    ->parameters(['ogrenciler' => 'ogrenci']);

Route::apiResource('egitmenler', EgitmenController::class)
    ->parameters(['egitmenler' => 'egitmen']);

Route::apiResource('dersler', DersController::class)
    ->parameters(['dersler' => 'ders']);

Route::apiResource('notlar', NotController::class)
    ->parameters(['notlar' => 'not']);

// Alias: birimler -> dersler index (filtreleme için ?d="mali" vb. desteklenir)
Route::get('birimler', [DersController::class, 'index'])->name('birimler.index');
