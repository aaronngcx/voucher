<?php

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoucherController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate', [VoucherController::class, 'generateVouchers']);

Route::get('/test', function (Request $request) {
    ini_set('memory_limit', '2048M');
    set_time_limit(300);
    $perPage = 1000; 
    $page = $request->get('page', 1); 
    $start = ($page - 1) * $perPage;

    $cursor = 0;
    $voucherCodes = [];
    $currentCount = 0;

    do {
        // SSCAN to get small chunks of Redis set members
        [$cursor, $results] = Redis::sscan('voucher_codes', $cursor);
        // Ensure $results is an array
        if (is_array($results)) {
            foreach ($results as $code) {
                if ($currentCount >= $start && $currentCount < $start + $perPage) {
                    $voucherCodes[] = $code;
                }
                $currentCount++;

                // Stop when the required page is filled
                if (count($voucherCodes) >= $perPage) {
                    break;
                }
            }
        } else {
            // Handle the case where $results is not an array
            return response()->json(['error' => 'No results returned from Redis'], 500);
        }

    } while ($cursor != 0 && count($voucherCodes) < $perPage);

    // Return paginated results
    return response()->json([
        'voucher_codes' => $voucherCodes,
        'page' => $page,
        'total_retrieved' => count($voucherCodes)
    ]);
});
