<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDownloadRequest;
use App\Models\Download;
use App\Services\DownloadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DownloadController extends Controller
{
    protected $downloadService;

    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $downloads = $request->user()
            ->downloads()
            ->with(['service'])
            ->latest()
            ->paginate(10);

        return response()->json($downloads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDownloadRequest $request): JsonResponse
    {
        try {
            $download = $this->downloadService->initiateDownload(
                $request->user(),
                $request->url
            );

            return response()->json([
                'message' => 'Download initiated successfully',
                'download' => $download->load('service')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get download URL for a completed download.
     */
    public function getDownloadUrl(Download $download): JsonResponse
    {
        try {
            $url = $this->downloadService->getDownloadUrl($download);

            return response()->json([
                'url' => $url
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Download $download): JsonResponse
    {
        // Check if the download belongs to the user
        if ($download->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($download->load(['service']));
    }
}
