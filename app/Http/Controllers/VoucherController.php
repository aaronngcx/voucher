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
        GenerateVouchersParentJob::dispatch();
        return response()->json(['message' => 'Voucher generation started.']); 
    }

}
