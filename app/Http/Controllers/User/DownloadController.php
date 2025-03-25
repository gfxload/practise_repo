<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Service;
use App\Models\CachedFile;
use App\Services\DownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DownloadController extends Controller
{
    private $downloadService;

    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    public function index(Request $request)
    {
        $query = Download::query()
            ->where('user_id', auth()->id())
            ->forUser();

        if ($request->filled('service')) {
            $query->where('service_id', $request->service);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $downloads = $query->with(['service', 'videoOption'])
            ->latest()
            ->paginate(10);

        $services = Service::all();

        return view('downloads.index', compact('downloads', 'services'));
    }

    public function create()
    {
        $services = Service::active()->get();
        return view('downloads.create', compact('services'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasActiveSubscription()) {
            return redirect()->back()->with('error', 'Your subscription has expired. Please renew to continue downloading.');
        }

        Log::info('Received download request:', ['urls' => $request->urls]);

        try {
            $request->validate([
                'urls' => ['required', 'array', 'min:1'],
                'urls.*.url' => ['required', 'url'],
                'urls.*.service_id' => ['required', 'exists:services,id'],
                'urls.*.image_url' => ['required', 'url'],
                'urls.*.file_id' => ['required', 'string'],
                'urls.*.video_option_id' => ['nullable', 'exists:video_options,id']
            ]);

            $urls = collect($request->urls);
            $totalPoints = 0;
            $downloads = [];

            // Calculate total points needed
            foreach ($urls as $urlData) {
                $service = Service::findOrFail($urlData['service_id']);
                $totalPoints += $service->points_cost;
                
                // إضافة تكلفة خيار الفيديو إذا تم تحديده
                if (isset($urlData['video_option_id']) && $service->is_video) {
                    $videoOption = $service->videoOptions()->find($urlData['video_option_id']);
                    if ($videoOption) {
                        $totalPoints += $videoOption->points_cost;
                        $videoOptionId = $videoOption->id;
                        
                        // تسجيل معلومات خيار الفيديو
                        Log::info('Video option selected for download', [
                            'service_id' => $service->id,
                            'service_name' => $service->name,
                            'video_option_id' => $videoOption->id,
                            'display_name' => $videoOption->display_name,
                            'parameter_name' => $videoOption->parameter_name,
                            'parameter_value' => $videoOption->parameter_value,
                            'points_cost' => $videoOption->points_cost
                        ]);
                    } else {
                        Log::warning('Video option not found', [
                            'service_id' => $service->id,
                            'video_option_id' => $urlData['video_option_id']
                        ]);
                    }
                } else if ($service->is_video) {
                    // إذا كانت الخدمة فيديو ولكن لم يتم تحديد خيار فيديو، نستخدم الخيار الافتراضي
                    $defaultVideoOption = $service->videoOptions()->where('points_cost', 0)->first();
                    if ($defaultVideoOption) {
                        $videoOptionId = $defaultVideoOption->id;
                        
                        // تسجيل معلومات خيار الفيديو الافتراضي
                        Log::info('Default video option selected for download', [
                            'service_id' => $service->id,
                            'service_name' => $service->name,
                            'video_option_id' => $defaultVideoOption->id,
                            'display_name' => $defaultVideoOption->display_name,
                            'parameter_name' => $defaultVideoOption->parameter_name,
                            'parameter_value' => $defaultVideoOption->parameter_value,
                            'points_cost' => $defaultVideoOption->points_cost
                        ]);
                    } else {
                        // إذا لم يكن هناك خيار افتراضي، نستخدم أول خيار متاح
                        $firstVideoOption = $service->videoOptions()->first();
                        if ($firstVideoOption) {
                            $videoOptionId = $firstVideoOption->id;
                            $totalPoints += $firstVideoOption->points_cost;
                            
                            // تسجيل معلومات أول خيار فيديو متاح
                            Log::info('First available video option selected for download', [
                                'service_id' => $service->id,
                                'service_name' => $service->name,
                                'video_option_id' => $firstVideoOption->id,
                                'display_name' => $firstVideoOption->display_name,
                                'parameter_name' => $firstVideoOption->parameter_name,
                                'parameter_value' => $firstVideoOption->parameter_value,
                                'points_cost' => $firstVideoOption->points_cost
                            ]);
                        } else {
                            Log::warning('No video options available for video service', [
                                'service_id' => $service->id,
                                'service_name' => $service->name
                            ]);
                        }
                    }
                }
            }

            // Check if user has enough points
            if ($user->points < $totalPoints) {
                return back()->withInput()->withErrors(['error' => 'Insufficient points. You need ' . $totalPoints . ' points but have ' . $user->points]);
            }

            DB::beginTransaction();

            foreach ($urls as $urlData) {
                $service = Service::findOrFail($urlData['service_id']);
                
                $downloadPointsCost = $service->points_cost;
                $videoOptionId = null;
                
                // إضافة تكلفة خيار الفيديو إذا تم تحديده
                if (isset($urlData['video_option_id']) && $service->is_video) {
                    $videoOption = $service->videoOptions()->find($urlData['video_option_id']);
                    if ($videoOption) {
                        $downloadPointsCost += $videoOption->points_cost;
                        $videoOptionId = $videoOption->id;
                        
                        // تسجيل معلومات خيار الفيديو
                        Log::info('Video option selected for download', [
                            'service_id' => $service->id,
                            'service_name' => $service->name,
                            'video_option_id' => $videoOption->id,
                            'display_name' => $videoOption->display_name,
                            'parameter_name' => $videoOption->parameter_name,
                            'parameter_value' => $videoOption->parameter_value,
                            'points_cost' => $videoOption->points_cost
                        ]);
                    } else {
                        Log::warning('Video option not found', [
                            'service_id' => $service->id,
                            'video_option_id' => $urlData['video_option_id']
                        ]);
                    }
                } else if ($service->is_video) {
                    // إذا كانت الخدمة فيديو ولكن لم يتم تحديد خيار فيديو، نستخدم الخيار الافتراضي
                    $defaultVideoOption = $service->videoOptions()->where('points_cost', 0)->first();
                    if ($defaultVideoOption) {
                        $videoOptionId = $defaultVideoOption->id;
                        
                        // تسجيل معلومات خيار الفيديو الافتراضي
                        Log::info('Default video option selected for download', [
                            'service_id' => $service->id,
                            'service_name' => $service->name,
                            'video_option_id' => $defaultVideoOption->id,
                            'display_name' => $defaultVideoOption->display_name,
                            'parameter_name' => $defaultVideoOption->parameter_name,
                            'parameter_value' => $defaultVideoOption->parameter_value,
                            'points_cost' => $defaultVideoOption->points_cost
                        ]);
                    } else {
                        // إذا لم يكن هناك خيار افتراضي، نستخدم أول خيار متاح
                        $firstVideoOption = $service->videoOptions()->first();
                        if ($firstVideoOption) {
                            $videoOptionId = $firstVideoOption->id;
                            $downloadPointsCost += $firstVideoOption->points_cost;
                            
                            // تسجيل معلومات أول خيار فيديو متاح
                            Log::info('First available video option selected for download', [
                                'service_id' => $service->id,
                                'service_name' => $service->name,
                                'video_option_id' => $firstVideoOption->id,
                                'display_name' => $firstVideoOption->display_name,
                                'parameter_name' => $firstVideoOption->parameter_name,
                                'parameter_value' => $firstVideoOption->parameter_value,
                                'points_cost' => $firstVideoOption->points_cost
                            ]);
                        } else {
                            Log::warning('No video options available for video service', [
                                'service_id' => $service->id,
                                'service_name' => $service->name
                            ]);
                        }
                    }
                }
                
                $download = Download::create([
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'original_url' => $urlData['url'],
                    'image_url' => $urlData['image_url'],
                    'file_id' => $urlData['file_id'],
                    'video_option_id' => $videoOptionId,
                    'points_spent' => $downloadPointsCost,
                    'status' => 'pending'
                ]);

                $downloads[] = $download;
                $user->points -= $downloadPointsCost;
            }

            $user->save();
            DB::commit();

            // Dispatch download jobs
            foreach ($downloads as $download) {
                try {
                    $this->downloadService->processDownload(
                        $download->original_url,
                        $download->service_id,
                        $download->file_id,
                        $download->video_option_id
                    );
                } catch (\Exception $e) {
                    // التحقق إذا كانت الحالة processing
                    if (str_contains($e->getMessage(), 'being processed')) {
                        Log::info('Download is being processed:', [
                            'download_id' => $download->id,
                            'message' => $e->getMessage()
                        ]);
                    } else {
                        // في حالة الأخطاء الأخرى
                        Log::error('Error processing download:', [
                            'download_id' => $download->id,
                            'error' => $e->getMessage()
                        ]);
                        
                        // تحديث حالة التحميل إلى فشل
                        $download->markAsFailed($e->getMessage());
                    }
                }
            }

            return redirect()->route('downloads.index')
                ->with('success', count($downloads) . ' download(s) initiated successfully!');

        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Error processing download:', ['error' => $e->getMessage()]);
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'An error occurred while processing your download. Please try again.']);
        }
    }

    public function show(Download $download)
    {
        if ($download->user_id !== auth()->id()) {
            abort(403);
        }

        $isAdminRoute = str_contains(request()->path(), 'admin');
        if (!$isAdminRoute && $download->isExpired()) {
            abort(404, 'This download has expired. Please download the file again.');
        }

        // تحميل علاقة videoOption إذا لم تكن محملة بالفعل
        if (!$download->relationLoaded('videoOption')) {
            $download->load(['videoOption', 'service']);
        }

        return view('downloads.show', compact('download'));
    }

    public function download(Download $download)
    {
        if (! Gate::allows('view', $download)) {
            abort(403);
        }

        // Find cached file
        $cachedFile = CachedFile::where('service_id', $download->service_id)
            ->where('file_id', $download->file_id)
            ->first();

        if (!$cachedFile) {
            Log::error('Cached file not found for download:', [
                'download_id' => $download->id,
                'service_id' => $download->service_id,
                'file_id' => $download->file_id
            ]);
            return back()->withErrors(['error' => 'File not found in storage']);
        }

        if (!Storage::exists($cachedFile->path)) {
            Log::error('File missing from storage:', [
                'cached_file_id' => $cachedFile->id,
                'path' => $cachedFile->path
            ]);
            return back()->withErrors(['error' => 'File not found in storage']);
        }

        Log::info('Serving download:', [
            'download_id' => $download->id,
            'cached_file_id' => $cachedFile->id,
            'path' => $cachedFile->path
        ]);

        $cachedFile->updateLastAccessed();
        
        return Storage::download(
            $cachedFile->path, 
            $cachedFile->original_filename
        );
    }
}
