<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Jobs\GenerateVouchersParentJob; // Import the parent job

class VoucherController extends Controller
{

    public function generateVouchers()
    {
        Redis::del('voucher_codes'); // Delete the entire set
        dispatch(new GenerateVouchersParentJob());
        return response()->json(['message' => 'Voucher generation started.']); 
    }

}
