<?php

namespace App\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateVouchersParentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        $totalVouchers = 3000000;
        $batchSize = 100000; // Number of vouchers per job
        $jobsCount = ceil($totalVouchers / $batchSize);
        $jobs = [];

        for ($i = 0; $i < $jobsCount; $i++) {
            $jobs[] = new GenerateVouchers($batchSize, $i * $batchSize);
        }

        // Batch the jobs and ensure the export job runs after all are completed
        Bus::batch($jobs)
            ->then(function (Batch $batch) {
                // Dispatch the export job after all voucher jobs are completed
                Log::info('All voucher generation jobs completed. Dispatching ExportVouchers job.');
                ExportVouchers::dispatch();
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('Voucher generation batch failed: ' . $e->getMessage());
            })
            ->finally(function (Batch $batch) {
                Log::info('Voucher generation batch processing finished.');
            })
            ->dispatch();
    }
}
