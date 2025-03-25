<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\CachedFile;
use App\Models\Download;
use App\Models\Setting;
use App\Jobs\ProcessDownload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\File;

class DownloadService
{
    private $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.gfxload.api_key');
    }

    public function processDownload($url, $serviceId, $fileId, $videoOptionId = null)
    {
        try {
            // Get download record
            $download = Download::where('original_url', $url)
                ->where('service_id', $serviceId)
                ->where('file_id', $fileId)
                ->latest()
                ->first();

            if (!$download) {
                throw new \Exception('Download record not found');
            }

            // Check if file is already cached
            $cachedFile = CachedFile::findByServiceAndFileId($serviceId, $fileId);
            if ($cachedFile) {
                Log::info('File found in cache', ['cached_file' => $cachedFile->id]);
                $cachedFile->updateLastAccessed();
                $download->markAsCompleted($cachedFile->path);
                return $this->streamCachedFile($cachedFile);
            }

            // Get download method from settings
            $method = Setting::get('download_method', 'method1');
            Log::info('Using download method:', ['method' => $method]);

            // Process download based on method
            try {
                $result = $method === 'method1' 
                    ? $this->processMethodOne($url, $serviceId, $fileId, $videoOptionId)
                    : $this->processMethodTwo($url, $serviceId, $fileId, null, $videoOptionId);

                if ($result) {
                    $download->markAsCompleted($result['local_path']);
                }
                return $result;
            } catch (\Exception $e) {
                // إذا كانت الحالة processing، نرمي لاستثناء للأعلى
                if (str_contains($e->getMessage(), 'being processed')) {
                    throw $e;
                }
                // في حالة اأخطاء الأخرى
                Log::error('Download method failed:', [
                    'error' => $e->getMessage(),
                    'method' => $method
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            // سجيل الخطأ
            Log::error('Download processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // إذا كان الحالة processing، لا نغير حالة التحمي
            if (!str_contains($e->getMessage(), 'being processed')) {
                $download->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    private function processMethodOne($url, $serviceId, $fileId, $videoOptionId = null)
    {
        Log::info('Using Method 1 for download', ['url' => $url]);

        $params = [
            'api' => $this->apiKey,
            'url' => $url
        ];

        // إضافة معلمات خيار الفييو إذا تم تحديده
        if ($videoOptionId) {
            $videoOption = \App\Models\VideoOption::find($videoOptionId);
            if ($videoOption) {
                $params[$videoOption->parameter_name] = $videoOption->parameter_value;
                Log::info('Adding video option parameters', [
                    'video_option_id' => $videoOption->id,
                    'parameter_name' => $videoOption->parameter_name,
                    'parameter_value' => $videoOption->parameter_value,
                    'display_name' => $videoOption->display_name,
                    'points_cost' => $videoOption->points_cost
                ]);
            } else {
                Log::warning('Video option not found', ['video_option_id' => $videoOptionId]);
            }
        }

        // Get the download record for logging
        $download = Download::where('original_url', $url)
            ->where('service_id', $serviceId)
            ->where('file_id', $fileId)
            ->latest()
            ->first();

        $userId = $download ? $download->user_id : (Auth::check() ? Auth::id() : null);
        $downloadId = $download ? $download->id : null;

        try {
            $response = Http::get("https://web.gfxload.com/unpub/", $params);

            // Log API request to database
            $apiLog = ApiLog::create([
                'method' => 'method1',
                'api_type' => null,
                'url' => "https://web.gfxload.com/unpub/",
                'status' => $response->successful() ? 'success' : 'failed',
                'http_status' => $response->status(),
                'request_data' => $params,
                'response_data' => $response->json() ?: ['raw' => $response->body()],
                'user_id' => $userId,
                'download_id' => $downloadId,
            ]);

            if (!$response->successful()) {
                Log::error('Method 1 download failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'api_log_id' => $apiLog->id
                ]);
                throw new \Exception('Failed to download file: ' . $response->body());
            }

            $data = $response->json();
            Log::info('Method 1 download successful', [
                'response_data' => $data,
                'api_log_id' => $apiLog->id
            ]);

            // Download and cache the file
            return $this->downloadAndCacheFile(
                $data['downloadURL'], 
                $data['filename'], 
                $data['filesizeinbytes'], 
                $serviceId, 
                $fileId
            );
        } catch (\Exception $e) {
            // Log error if API log was not created
            if (!isset($apiLog)) {
                ApiLog::create([
                    'method' => 'method1',
                    'api_type' => null,
                    'url' => "https://web.gfxload.com/unpub/",
                    'status' => 'failed',
                    'request_data' => $params,
                    'response_data' => ['error' => $e->getMessage()],
                    'user_id' => $userId,
                    'download_id' => $downloadId,
                ]);
            }
            throw $e;
        }
    }

    /**
     * Process download using method two.
     */
    public function processMethodTwo($url, $serviceId, $fileId, $orderId = null, $videoOptionId = null)
    {
        Log::info('Using Method 2 for download', ['url' => $url]);

        // Get the download record for logging
        $download = Download::where('original_url', $url)
            ->where('service_id', $serviceId)
            ->where('file_id', $fileId)
            ->latest()
            ->first();

        $userId = $download ? $download->user_id : (Auth::check() ? Auth::id() : null);
        $downloadId = $download ? $download->id : null;

        try {
            // إذا تم تمرير order_id، استخدمه مبارة
            if ($orderId) {
                $orderResponse = [
                    'status' => true,
                    'orderid' => $orderId,
                    'message' => 'processing'
                ];
            } else {
                // وضع الطلب
                $orderResponse = $this->placeOrder($url, $videoOptionId, $userId, $downloadId);
            }

            if (!$orderResponse['status']) {
                throw new \Exception('Failed to place order: ' . ($orderResponse['message'] ?? 'Unknown error'));
            }

            $orderId = $orderResponse['orderid'];
            Log::info('Order placed successfully', [
                'order_id' => $orderId,
                'response_data' => $orderResponse
            ]);

            // محاولة تحميل املف
            $downloadResponse = $this->downloadFile($orderId, $userId, $downloadId);
            Log::info('Method 2 download response', [
                'response_data' => $downloadResponse,
                'order_id' => $orderId
            ]);

            // إذا كان الملف ما زال قيد المعالجة
            if (!$downloadResponse['status'] && isset($downloadResponse['message']) && $downloadResponse['message'] === 'processing') {
                // تحديث حالة التحميل إلى processing
                if ($download) {
                    $download->markAsProcessing($orderId);
                }

                throw new \Exception('Download is being processed. Please check back later.');
            }

            // إذا نجح التحميل
            if ($downloadResponse['status'] && isset($downloadResponse['downloadURL'])) {
                Log::info('Method 2 download successful', [
                    'download_url' => $downloadResponse['downloadURL'],
                    'filename' => $downloadResponse['filename'],
                    'size' => $downloadResponse['filesizeinbytes']
                ]);

                // تحميل وتخزين الملف
                return $this->downloadAndCacheFile(
                    $downloadResponse['downloadURL'],
                    $downloadResponse['filename'],
                    $downloadResponse['filesizeinbytes'],
                    $serviceId,
                    $fileId
                );
            }

            throw new \Exception('Invalid download response: ' . json_encode($downloadResponse));
        } catch (\Exception $e) {
            Log::error('Method 2 download failed:', [
                'error' => $e->getMessage(),
                'url' => $url,
                'order_id' => $orderId ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function placeOrder($url, $videoOptionId = null, $userId = null, $downloadId = null)
    {
        $params = [
            'apikey' => $this->apiKey,
            'url' => $url
        ];

        // إضافة معمات خيار الفيديو إذا تم تحديده
        if ($videoOptionId) {
            $videoOption = \App\Models\VideoOption::find($videoOptionId);
            if ($videoOption) {
                $params[$videoOption->parameter_name] = $videoOption->parameter_value;
                Log::info('Adding video option parameters to order', [
                    'video_option_id' => $videoOption->id,
                    'parameter_name' => $videoOption->parameter_name,
                    'parameter_value' => $videoOption->parameter_value,
                    'display_name' => $videoOption->display_name,
                    'points_cost' => $videoOption->points_cost
                ]);
            } else {
                Log::warning('Video option not found', ['video_option_id' => $videoOptionId]);
            }
        }

        try {
            $orderResponse = Http::get("https://web.gfxload.com/order", $params);

            // Log API request to database
            $apiLog = ApiLog::create([
                'method' => 'method2',
                'api_type' => 'order',
                'url' => "https://web.gfxload.com/order",
                'status' => $orderResponse->successful() ? 'success' : 'failed',
                'http_status' => $orderResponse->status(),
                'request_data' => $params,
                'response_data' => $orderResponse->json() ?: ['raw' => $orderResponse->body()],
                'user_id' => $userId,
                'download_id' => $downloadId,
            ]);

            if (!$orderResponse->successful()) {
                Log::error('Method 2 order failed', [
                    'status' => $orderResponse->status(),
                    'body' => $orderResponse->body(),
                    'api_log_id' => $apiLog->id
                ]);
                throw new \Exception('Failed to place order: ' . $orderResponse->body());
            }

            // تسجيل الرد الكامل للشخيص
            Log::info('Order API response', [
                'status' => $orderResponse->status(),
                'headers' => $orderResponse->headers(),
                'body' => $orderResponse->body(),
                'json' => $orderResponse->json(),
                'api_log_id' => $apiLog->id
            ]);

            $orderData = $orderResponse->json();
            
            // لتحقق من وجود orderid في الرد
            if (!isset($orderData['orderid'])) {
                Log::error('Order response missing orderid', [
                    'response' => $orderData,
                    'raw_response' => $orderResponse->body(),
                    'content_type' => $orderResponse->header('Content-Type'),
                    'api_log_id' => $apiLog->id
                ]);
                throw new \Exception('Invalid order response: orderid not found');
            }

            // Update the API log with the order ID
            if (isset($orderData['orderid'])) {
                $apiLog->update(['order_id' => $orderData['orderid']]);
            }

            return $orderData;
        } catch (\Exception $e) {
            // Log error if API log was not created
            if (!isset($apiLog)) {
                ApiLog::create([
                    'method' => 'method2',
                    'api_type' => 'order',
                    'url' => "https://web.gfxload.com/order",
                    'status' => 'failed',
                    'request_data' => $params,
                    'response_data' => ['error' => $e->getMessage()],
                    'user_id' => $userId,
                    'download_id' => $downloadId,
                ]);
            }
            throw $e;
        }
    }

    private function downloadFile($orderId, $userId = null, $downloadId = null)
    {
        $params = [
            'apikey' => $this->apiKey,
            'orderid' => $orderId
        ];

        try {
            $downloadResponse = Http::get("https://web.gfxload.com/download", $params);

            // Log API request to database
            $apiLog = ApiLog::create([
                'method' => 'method2',
                'api_type' => 'download',
                'url' => "https://web.gfxload.com/download",
                'order_id' => $orderId,
                'status' => $downloadResponse->successful() ? 'success' : 'failed',
                'http_status' => $downloadResponse->status(),
                'request_data' => $params,
                'response_data' => $downloadResponse->json() ?: ['raw' => $downloadResponse->body()],
                'user_id' => $userId,
                'download_id' => $downloadId,
            ]);

            if (!$downloadResponse->successful()) {
                throw new \Exception('Download request failed with status: ' . $downloadResponse->status());
            }

            $downloadData = $downloadResponse->json();
            if (!$downloadData) {
                throw new \Exception('Invalid download response format');
            }

            Log::info('Method 2 download response', [
                'response_data' => $downloadData,
                'order_id' => $orderId,
                'api_log_id' => $apiLog->id
            ]);

            return $downloadData;
        } catch (\Exception $e) {
            // Log error if API log was not created
            if (!isset($apiLog)) {
                ApiLog::create([
                    'method' => 'method2',
                    'api_type' => 'download',
                    'url' => "https://web.gfxload.com/download",
                    'order_id' => $orderId,
                    'status' => 'failed',
                    'request_data' => $params,
                    'response_data' => ['error' => $e->getMessage()],
                    'user_id' => $userId,
                    'download_id' => $downloadId,
                ]);
            }
            throw $e;
        }
    }

    private function downloadAndCacheFile($downloadUrl, $filename, $fileSize, $serviceId, $fileId)
    {
        try {
            Log::info('Downloading and caching file', [
                'url' => $downloadUrl,
                'filename' => $filename,
                'size' => $fileSize
            ]);

            // التقق من وجود الملف ي الكاش
            $existingFile = CachedFile::where('service_id', $serviceId)
                ->where('file_id', $fileId)
                ->first();

            if ($existingFile) {
                Log::info('File already exists in cache', [
                    'cached_file_id' => $existingFile->id,
                    'path' => $existingFile->path
                ]);
                
                // تحديث وقت آخر استخدام
                $existingFile->updateLastAccessed();
                
                return [
                    'local_path' => $existingFile->path,
                    'file_size' => $existingFile->file_size
                ];
            }

            // إنشاء اسم فريد لملف
            $storedFilename = Str::random(40) . '_' . $filename;
            $relativePath = 'downloads/' . $storedFilename;
            $fullPath = storage_path('app/private/' . $relativePath);

            // التأكد من وجود الجلد
            if (!File::exists(dirname($fullPath))) {
                File::makeDirectory(dirname($fullPath), 0755, true);
            }

            // تحميل الملف
            $response = Http::withOptions([
                'sink' => $fullPath,
                'timeout' => 3000
            ])->get($downloadUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download file: ' . $response->status());
            }

            // فظ معلومات الملف في قاعدة البيانات
            $cachedFile = CachedFile::create([
                'service_id' => $serviceId,
                'file_id' => $fileId,
                'original_filename' => $filename,
                'stored_filename' => $storedFilename,
                'file_size' => $fileSize,
                'path' => $relativePath,
                'last_accessed_at' => now()
            ]);

            Log::info('File cached successfully', [
                'cached_file_id' => $cachedFile->id,
                'path' => $relativePath
            ]);

            return [
                'local_path' => $relativePath,
                'file_size' => $fileSize
            ];

        } catch (\Exception $e) {
            Log::error('Failed to cache file:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to cache downloaded file: ' . $e->getMessage());
        }
    }

    private function streamCachedFile(CachedFile $cachedFile)
    {
        if (!Storage::exists($cachedFile->path)) {
            Log::error('Cached file not found:', [
                'cached_file' => $cachedFile->id,
                'path' => $cachedFile->path
            ]);
            throw new \Exception('Cached file not found in storage');
        }

        Log::info('Streaming cached file', [
            'cached_file' => $cachedFile->id,
            'path' => $cachedFile->path
        ]);

        return Storage::download(
            $cachedFile->path, 
            $cachedFile->original_filename
        );
    }
}
