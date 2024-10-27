<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportVouchersToCsv implements ShouldQueue
{
    use Dispatchable;

    public $timeout = 180;

    public function handle()
    {
        Log::info('Begin export to CSV');
        ini_set('memory_limit', '2048M');
        set_time_limit(300);
        $totalCodes = Redis::smembers('voucher_codes');

        if (empty($totalCodes)) {
            Log::info("No voucher codes to export.");
            return;
        }

        Log::info('Obtained codes from redis and exporting now');
        $filePath = storage_path('app/vouchers.csv');
        $fileHandle = fopen($filePath, 'w');
        if ($fileHandle === false) {
            Log::error("Failed to open the file for writing: {$filePath}");
            return;
        }

        foreach ($totalCodes as $code) {
            fputcsv($fileHandle, [$code]);
        }

        fclose($fileHandle);
        Redis::set('csv_ready', 'true');
        Log::info("Exported " . count($totalCodes) . " voucher codes to {$filePath}.");
        
        Redis::del('voucher_codes');
    }
}
