<?php

namespace App\Http\Controllers;

use League\Csv\Writer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Jobs\GenerateVouchersParentJob;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VoucherController extends Controller
{

    public function generateVouchers()
    {
        Redis::set('csv_ready', 'false');
        $filePath = storage_path('app/vouchers.csv');

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        Redis::del('voucher_codes');
        dispatch(new GenerateVouchersParentJob());
        return view('generate-vouchers');
    }

    public function checkCsvStatus()
    {
        if (Redis::get('csv_ready') == 'true') {
            $filePath = storage_path('app/vouchers.csv');

            if (file_exists($filePath)) {
                $downloadUrl = asset('storage/app/vouchers.csv');
                return response()->json(['download_url' => $downloadUrl]);
            }
        }

        return response()->json(['download_url' => null]);
    }
}
