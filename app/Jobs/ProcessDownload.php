<?php

namespace App\Jobs;

use App\Models\Download;
use App\Services\DownloadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60; // 1 minute

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    protected $download;

    /**
     * Create a new job instance.
     */
    public function __construct(Download $download)
    {
        $this->download = $download;
        $this->onQueue('downloads');
    }

    /**
     * Execute the job.
     */
    public function handle(DownloadService $downloadService): void
    {
        try {
            Log::info('Processing download job', [
                'download_id' => $this->download->id,
                'order_id' => $this->download->order_id,
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries
            ]);

            // تحقق من أن التحميل ما زال في حالة processing
            if (!$this->download->isProcessing()) {
                Log::info('Download is no longer processing, skipping retry', [
                    'download_id' => $this->download->id,
                    'current_status' => $this->download->status
                ]);
                return;
            }

            // محاولة معالجة التحميل مرة أخرى
            try {
                $result = $downloadService->processMethodTwo(
                    $this->download->original_url,
                    $this->download->service_id,
                    $this->download->file_id,
                    $this->download->order_id
                );

                // إذا نجح التحميل، قم بتحديث حالة التحميل
                if ($result && isset($result['local_path'])) {
                    $this->download->markAsCompleted($result['local_path']);
                    Log::info('Download completed successfully after retry', [
                        'download_id' => $this->download->id,
                        'local_path' => $result['local_path']
                    ]);
                }
            } catch (\Exception $e) {
                // إذا كان الخطأ يشير إلى أن التحميل ما زال قيد المعالجة
                if (str_contains($e->getMessage(), 'being processed')) {
                    if ($this->attempts() < $this->tries) {
                        Log::info('Download still processing, scheduling next retry', [
                            'download_id' => $this->download->id,
                            'attempt' => $this->attempts(),
                            'next_attempt' => 'in ' . $this->backoff . ' seconds'
                        ]);
                        
                        $this->release($this->backoff);
                        return;
                    }

                    // إذا وصلنا للحد الأقصى من المحاولات
                    Log::warning('Max retry attempts reached while processing', [
                        'download_id' => $this->download->id,
                        'max_attempts' => $this->tries
                    ]);
                    $this->download->markAsFailed('Max retry attempts reached while processing');
                    return;
                }

                // في حالة الأخطاء الأخرى
                Log::error('Download retry failed with error', [
                    'download_id' => $this->download->id,
                    'error' => $e->getMessage(),
                    'attempt' => $this->attempts()
                ]);
                
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Download processing failed', [
                'download_id' => $this->download->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // فقط قم بتحديث الحالة إلى failed إذا لم يكن الخطأ متعلق بـ processing
            if (!str_contains($e->getMessage(), 'being processed')) {
                $this->download->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Download job failed', [
            'download_id' => $this->download->id,
            'error' => $exception->getMessage(),
            'attempt' => $this->attempts()
        ]);

        $this->download->markAsFailed($exception->getMessage());
    }
}
