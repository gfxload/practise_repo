<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Services\DownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    protected $downloadService;

    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    public function download(Download $download)
    {
        try {
            return $this->downloadService->processDownload(
                $download->original_url,
                $download->service_id,
                $download->file_id
            );
        } catch (\Exception $e) {
            Log::error('Admin download failed:', [
                'download_id' => $download->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Failed to process download: ' . $e->getMessage()]);
        }
    }
}
