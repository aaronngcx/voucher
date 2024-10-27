<?php

namespace App\Jobs;

use Illuminate\Support\Str;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateVouchers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $timeout = 3600; // Set timeout to 1 hour
    protected $batchSize;
    protected $offset;

    /**
     * Create a new job instance.
     */
    public function __construct($batchSize, $offset)
    {
        $this->batchSize = $batchSize;
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $startTime = microtime(true);
        Log::info("Starting voucher generation for offset {$this->offset} with batch size {$this->batchSize}.");

        $codes = [];
        for ($i = 0; $i < $this->batchSize; $i++) {
            do {
                $code = Str::random(10);
            } while (Redis::sismember('voucher_codes', $code));
            
            $codes[] = $code;

            if (count($codes) % 10000 === 0) {
                Log::info("Generated " . count($codes) . " vouchers.");
            }
        }

        Log::info("Codes generated: " . count($codes));

        foreach ($codes as $code) {
            Redis::sadd('voucher_codes', $code);
        }
        Redis::expire('voucher_codes', 3600);

        Log::info("Codes added to Redis.");
        $elapsedTime = microtime(true) - $startTime;
        Log::info('Voucher generation completed.', [
            'batch_size' => $this->batchSize,
            'offset' => $this->offset,
            'time_taken' => round($elapsedTime, 2) . ' seconds',
        ]);
    }
}
