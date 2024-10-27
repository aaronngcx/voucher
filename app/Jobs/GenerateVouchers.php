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
        $uniqueCodesCount = 0;
        $retryCount = 0; // To limit retries for uniqueness
        $maxRetries = 5; // Adjust this based on your needs

        while ($uniqueCodesCount < $this->batchSize && $retryCount < $maxRetries) {
            $batch = [];

            for ($i = 0; $i < $this->batchSize - $uniqueCodesCount; $i++) {
                $code = Str::random(10);
                $batch[] = $code;
            }

            // Check for uniqueness in one go
            $uniqueBatch = array_filter($batch, function ($code) {
                return !Redis::sismember('voucher_codes', $code);
            });

            $codes = array_merge($codes, $uniqueBatch);
            $uniqueCodesCount += count($uniqueBatch);

            // Log at intervals to reduce log frequency
            if ($uniqueCodesCount % 10000 === 0) {
                Log::info("Generated " . $uniqueCodesCount . " unique vouchers.");
            }

            // If we reached the max retries, exit the loop
            if (count($batch) === count($uniqueBatch)) {
                break; // All generated codes were unique
            }

            $retryCount++;
        }

        // Bulk add unique codes to Redis
        if (!empty($codes)) {
            Redis::sadd('voucher_codes', ...$codes);
            Redis::expire('voucher_codes', 3600);
        }

        Log::info("Codes added to Redis. Total unique codes generated: " . count($codes));
        $elapsedTime = microtime(true) - $startTime;
        Log::info('Voucher generation completed.', [
            'batch_size' => $this->batchSize,
            'offset' => $this->offset,
            'time_taken' => round($elapsedTime, 2) . ' seconds',
        ]);
    }
}
