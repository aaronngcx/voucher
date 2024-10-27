<?php

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;

class GenerateVouchersParentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable;

    public function handle()
    {
        $totalVouchers = 3000000;
        $batchSize = 100000;
        $jobs = [];

        $startTime = microtime(true);

        for ($i = 0; $i < ceil($totalVouchers / $batchSize); $i++) {
            $jobs[] = new GenerateVouchers($batchSize, $i * $batchSize);
        }

        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($startTime) {
                $elapsedTime = microtime(true) - $startTime;

                Log::info('All voucher generation jobs completed.', [
                    'total_time_taken' => round($elapsedTime, 2) . ' seconds',
                ]);

            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('Voucher generation batch failed: ' . $e->getMessage());
            })
            ->finally(function (Batch $batch) {
                Log::info('Voucher generation batch completed.');
            })
            ->dispatch();
    }
}
