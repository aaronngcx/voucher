<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ExportVouchersToCsv implements ShouldQueue
{
    use Dispatchable;

    public $timeout = 180;
    public $tries = 3;

    public function handle()
    {
        Log::info('Begin export to CSV');
        ini_set('memory_limit', '2048M');
        set_time_limit(300);

        $filePath = storage_path('app/vouchers.csv');
        $fileHandle = fopen($filePath, 'w');

        if ($fileHandle === false) {
            Log::error("Failed to open the file for writing: {$filePath}");
            return;
        }

        $batchSize = 10000;
        $totalCodesCount = Redis::scard('voucher_codes');
        $processedCount = 0;

        while ($processedCount < $totalCodesCount) {
            $codes = Redis::srandmember('voucher_codes', $batchSize);

            if (empty($codes)) {
                break;
            }

            foreach ($codes as $code) {
                fputcsv($fileHandle, [$code]);
                $processedCount++;

                // Log progress every 500,000 codes
                if ($processedCount % 500000 == 0) {
                    Log::info("Processed {$processedCount} voucher codes...");
                }
            }
        }

        fclose($fileHandle);
        Redis::set('csv_ready', 'true');
        Redis::del('voucher_codes');
        Log::info("Exported {$totalCodesCount} voucher codes to {$filePath}.");
    }
}
