<?php

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoucherController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate', [VoucherController::class, 'generateVouchers'])->name('generate.vouchers');
Route::get('/check-csv-status', [VoucherController::class, 'checkCsvStatus'])->name('check.csv.status');

