<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use League\Csv\Writer;
use Illuminate\Support\Str;

class GenerateVouchers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $timeout = 3600; // Set timeout to 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
{
    $totalVouchers = 3000000; // Total voucher codes
    $batchSize = 50000; // Increased batch size
    $startTime = microtime(true);

    Log::info('Starting voucher generation.');

    for ($batch = 0; $batch < ceil($totalVouchers / $batchSize); $batch++) {
        $codes = [];
        for ($i = 0; $i < $batchSize; $i++) {
            do {
                $code = Str::random(10);
            } while (Redis::sismember('voucher_codes', $code));
            
            $codes[] = $code;

            if (count($codes) % 10000 === 0) {
                Log::info("Generated " . count($codes) . " vouchers in batch " . ($batch + 1) . ".");
            }
        }

        Redis::pipeline(function ($pipe) use ($codes) {
            foreach ($codes as $code) {
                $pipe->sadd('voucher_codes', $code);
            }
        });

        // Remove or reduce delay
        // usleep(100000); 
    }

    $elapsedTime = microtime(true) - $startTime;
    Log::info('Voucher generation completed.', [
        'total_vouchers' => $totalVouchers,
        'time_taken' => round($elapsedTime, 2) . ' seconds',
    ]);
}

}
