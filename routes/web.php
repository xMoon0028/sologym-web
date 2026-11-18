<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoloGymController;

/*
|--------------------------------------------------------------------------
| Web Routes for SoloGym Promotional & Infographic Website
|--------------------------------------------------------------------------
*/

Route::get('/', [SoloGymController::class, 'index'])->name('landing');
Route::get('/download/apk', [SoloGymController::class, 'downloadApk'])->name('download.apk');

// Interactive Web Widget APIs
Route::get('/api/calculate-plates', [SoloGymController::class, 'calculatePlates'])->name('api.calculate-plates');
Route::get('/api/calculate-body-comp', [SoloGymController::class, 'calculateBodyComp'])->name('api.calculate-body-comp');
