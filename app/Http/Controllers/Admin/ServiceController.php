<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\VideoOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('sort_order')->paginate(50);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        Log::info('Service creation request data:', $request->all());
        
        // التحقق من وجود is_video في الطلب
        if ($request->has('is_video')) {
            Log::info('Service is marked as video');
        }
        
        // التحقق من وجود video_options في الطلب
        if (isset($request->video_options)) {
            Log::info('Video options are present in the request', ['count' => count($request->video_options)]);
        }
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'points_cost' => 'required|integer|min:0',
                'url_pattern' => 'nullable|string|max:255',
                'file_id_pattern' => 'nullable|string|max:255',
                'expected_url_format' => 'nullable|string|max:255',
                'image' => 'nullable|image|max:2048',
                'video_options' => 'sometimes|array',
                'video_options.*.parameter_name' => 'required_with:video_options|string',
                'video_options.*.parameter_value' => 'required_with:video_options|string',
                'video_options.*.display_name' => 'required_with:video_options|string',
                'video_options.*.points_cost' => 'required_with:video_options|integer|min:0',
            ]);
            
            // تسجيل البيانات المتحقق منها
            Log::info('Validated service creation data:', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Unexpected error during validation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()])->withInput();
        }
        
        // إذا كانت الخدمة فيديو ولكن لا توجد خيارات فيديو
        if ($request->has('is_video') && (!isset($validated['video_options']) || empty($validated['video_options']))) {
            Log::warning('Video service without video options');
            return back()->withInput()->withErrors([
                'video_options' => 'You must add at least one video option for video services.'
            ]);
        }

        try {
            // Validate regex patterns if provided
            if (!empty($validated['url_pattern'])) {
                Log::info('Validating URL pattern', ['pattern' => $validated['url_pattern']]);
                try {
                    $this->validateRegexPattern($validated['url_pattern']);
                } catch (\Exception $e) {
                    Log::error('Invalid URL pattern', [
                        'pattern' => $validated['url_pattern'],
                        'error' => $e->getMessage()
                    ]);
                    return back()->withInput()->withErrors([
                        'url_pattern' => 'Invalid regex pattern: ' . $e->getMessage()
                    ]);
                }
            }
            
            if (!empty($validated['file_id_pattern'])) {
                Log::info('Validating file ID pattern', ['pattern' => $validated['file_id_pattern']]);
                try {
                    $this->validateRegexPattern($validated['file_id_pattern']);
                } catch (\Exception $e) {
                    Log::error('Invalid file ID pattern', [
                        'pattern' => $validated['file_id_pattern'],
                        'error' => $e->getMessage()
                    ]);
                    return back()->withInput()->withErrors([
                        'file_id_pattern' => 'Invalid regex pattern: ' . $e->getMessage()
                    ]);
                }
            }

            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'points_cost' => $validated['points_cost'],
                'is_active' => $request->has('is_active'),
                'is_video' => $request->has('is_video'),
                'url_pattern' => $validated['url_pattern'],
                'file_id_pattern' => $validated['file_id_pattern'],
                'expected_url_format' => $validated['expected_url_format'],
            ];

            Log::info('Preparing to create service with data', $data);

            if ($request->hasFile('image')) {
                Log::info('Processing image file');
                try {
                    $data['image_path'] = $request->file('image')->store('services', 'public');
                    Log::info('Image stored successfully', ['path' => $data['image_path']]);
                } catch (\Exception $e) {
                    Log::error('Error storing image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return back()->withInput()->withErrors([
                        'image' => 'Failed to store image: ' . $e->getMessage()
                    ]);
                }
            }

            try {
                Log::info('Creating service in database');
                $service = Service::create($data);
                
                Log::info('Service created successfully', [
                    'service_id' => $service->id,
                    'name' => $service->name,
                    'is_video' => $service->is_video
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating service in database', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $data
                ]);
                throw $e;
            }
            
            // Save video options if service is a video
            if ($request->has('is_video') && $request->has('video_options')) {
                Log::info('Creating video options for service', ['service_id' => $service->id, 'options_count' => count($request->video_options)]);
                
                try {
                    foreach ($request->video_options as $index => $option) {
                        // تسجيل بيانات كل خيار
                        Log::info('Processing video option data:', ['index' => $index, 'option' => $option]);
                        
                        // التحقق من وجود جميع الحقول المطلوبة
                        if (!isset($option['parameter_name']) || !isset($option['parameter_value']) || 
                            !isset($option['display_name']) || !isset($option['points_cost'])) {
                            Log::error('Missing required fields in video option', ['index' => $index, 'option' => $option]);
                            continue;
                        }
                        
                        // تحويل points_cost إلى عدد صحيح
                        $pointsCost = (int)$option['points_cost'];
                        
                        try {
                            // استخدام Eloquent مباشرة
                            $videoOption = new VideoOption([
                                'service_id' => $service->id,
                                'parameter_name' => $option['parameter_name'],
                                'parameter_value' => $option['parameter_value'],
                                'display_name' => $option['display_name'],
                                'points_cost' => $pointsCost,
                            ]);
                            $videoOption->save();
                            
                            Log::info('Video option created successfully using Eloquent', [
                                'video_option_id' => $videoOption->id,
                                'service_id' => $service->id,
                                'parameter_name' => $option['parameter_name'],
                                'parameter_value' => $option['parameter_value'],
                                'display_name' => $option['display_name'],
                                'points_cost' => $option['points_cost']
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error creating individual video option', [
                                'index' => $index,
                                'option' => $option,
                                'service_id' => $service->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                    
                    // التحقق من عدد خيارات الفيديو التي تم إنشاؤها
                    $createdOptions = VideoOption::where('service_id', $service->id)->count();
                    Log::info('Video options created count', [
                        'service_id' => $service->id,
                        'created_count' => $createdOptions,
                        'expected_count' => count($request->video_options)
                    ]);
                    
                    if ($createdOptions === 0 && count($request->video_options) > 0) {
                        Log::error('Failed to create any video options despite having options in request', [
                            'service_id' => $service->id,
                            'options_in_request' => $request->video_options
                        ]);
                        throw new \Exception('Failed to create video options.');
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error creating video options', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'service_id' => $service->id
                    ]);
                    throw $e;
                }
            }

            return redirect()
                ->route('admin.services.index')
                ->with('success', 'Service created successfully.');
        } catch (\Exception $e) {
            if (isset($service) && $service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            
            Log::error('Error creating service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create service. ' . $e->getMessage()]);
        }
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        // تسجيل بيانات الطلب
        Log::info('Service update request data:', $request->all());
        
        if ($request->has('is_video')) {
            Log::info('Service is marked as video');
        }
        
        // تحقق من وجود خيارات الفيديو وتسجيلها
        $hasVideoOptions = false;
        $videoOptionsData = [];
        
        try {
            if (isset($request->all()['video_options']) && is_array($request->all()['video_options'])) {
                $videoOptionsData = $request->all()['video_options'];
                $hasVideoOptions = true;
                Log::info('Video options are present in the request', ['count' => count($videoOptionsData)]);
            } else {
                Log::warning('No video options found in request or not in array format');
            }
        } catch (\Exception $e) {
            Log::error('Error checking video options:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        try {
            // Intentar validar con reglas mínimas primero
            $validated = $request->validate([
                'name' => ['required', 'string'],
                'description' => ['required', 'string'],
                'points_cost' => ['required'],
                'url_pattern' => ['required'],
                'file_id_pattern' => ['required'],
            ]);
            
            // Convertir points_cost a numérico
            $validated['points_cost'] = (float) $validated['points_cost'];
            
            // Manejar campos booleanos
            $validated['is_active'] = $request->has('is_active');
            $validated['is_video'] = $request->has('is_video');
            
            // Agregar campos opcionales si existen
            if ($request->has('expected_url_format')) {
                $validated['expected_url_format'] = $request->expected_url_format;
            }
            
            Log::info('Validated service update data:', $validated);
            Log::info('Video options validated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Unexpected error during validation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()])->withInput();
        }

        // إذا كانت الخدمة من نوع فيديو، تأكد من وجود خيارات فيديو
        if ($request->has('is_video') && !$hasVideoOptions) {
            Log::warning('Video service without video options submitted');
            return back()->withInput()->withErrors([
                'video_options' => 'You must add at least one video option for video services.'
            ]);
        }

        try {
            // Validate regex patterns if provided
            if (!empty($validated['url_pattern'])) {
                $this->validateRegexPattern($validated['url_pattern']);
            }
            if (!empty($validated['file_id_pattern'])) {
                $this->validateRegexPattern($validated['file_id_pattern']);
            }

            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'points_cost' => $validated['points_cost'],
                'is_active' => $request->has('is_active'),
                'is_video' => $request->has('is_video'),
                'url_pattern' => $validated['url_pattern'],
                'file_id_pattern' => $validated['file_id_pattern'],
                'expected_url_format' => $validated['expected_url_format'],
            ];

            if ($request->hasFile('image')) {
                // Delete old image if exists
                $service->deleteImage();
                
                // Store new image
                $data['image_path'] = $request->file('image')->store('services', 'public');
            }

            $service->update($data);
            
            // تسجيل معلومات الخدمة التي تم تحديثها
            Log::info('Service updated successfully', [
                'service_id' => $service->id,
                'name' => $service->name,
                'is_video' => $service->is_video
            ]);

            // Update video options if service is a video
            if ($request->has('is_video') && $hasVideoOptions) {
                Log::info('Starting video options update');
                
                try {
                    // Get existing option IDs
                    $existingOptionIds = $service->videoOptions()->pluck('id')->toArray();
                    Log::info('Existing video options', ['ids' => $existingOptionIds]);
                    
                    $updatedOptionIds = [];
                    
                    // Process each video option
                    foreach ($videoOptionsData as $option) {
                        Log::info('Processing video option', ['option' => $option]);
                        
                        // التحقق من وجود جميع الحقول المطلوبة
                        if (!isset($option['parameter_name']) || !isset($option['parameter_value']) || 
                            !isset($option['display_name']) || !isset($option['points_cost'])) {
                            Log::error('Missing required fields in video option', ['option' => $option]);
                            continue;
                        }
                        
                        // تحويل points_cost إلى عدد صحيح
                        $pointsCost = (int)$option['points_cost'];
                        
                        if (isset($option['id']) && !empty($option['id'])) {
                            // Update existing option
                            $videoOption = VideoOption::find($option['id']);
                            if ($videoOption && $videoOption->service_id == $service->id) {
                                $videoOption->parameter_name = $option['parameter_name'];
                                $videoOption->parameter_value = $option['parameter_value'];
                                $videoOption->display_name = $option['display_name'];
                                $videoOption->points_cost = $pointsCost;
                                $videoOption->save();
                                
                                $updatedOptionIds[] = $videoOption->id;
                                Log::info('Video option updated successfully', [
                                    'video_option_id' => $videoOption->id,
                                    'service_id' => $service->id
                                ]);
                            } else {
                                Log::warning('Video option not found or does not belong to this service', [
                                    'option_id' => $option['id'],
                                    'service_id' => $service->id
                                ]);
                            }
                        } else {
                            // Create new option
                            $videoOption = new VideoOption();
                            $videoOption->service_id = $service->id;
                            $videoOption->parameter_name = $option['parameter_name'];
                            $videoOption->parameter_value = $option['parameter_value'];
                            $videoOption->display_name = $option['display_name'];
                            $videoOption->points_cost = $pointsCost;
                            $videoOption->save();
                            
                            $updatedOptionIds[] = $videoOption->id;
                            Log::info('Video option created successfully', [
                                'video_option_id' => $videoOption->id,
                                'service_id' => $service->id,
                                'parameter_name' => $option['parameter_name'],
                                'parameter_value' => $option['parameter_value']
                            ]);
                        }
                    }
                    
                    // Delete options that were not updated
                    $optionsToDelete = VideoOption::where('service_id', $service->id)
                        ->whereNotIn('id', $updatedOptionIds)
                        ->get();
                    
                    $deletedCount = 0;
                    foreach ($optionsToDelete as $optionToDelete) {
                        Log::info('Deleting video option', [
                            'video_option_id' => $optionToDelete->id,
                            'service_id' => $service->id,
                            'parameter_name' => $optionToDelete->parameter_name,
                            'parameter_value' => $optionToDelete->parameter_value
                        ]);
                        $optionToDelete->delete();
                        $deletedCount++;
                    }
                    
                    Log::info('Video options update completed', [
                        'service_id' => $service->id,
                        'updated_count' => count($updatedOptionIds),
                        'deleted_count' => $deletedCount
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error updating video options', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'service_id' => $service->id
                    ]);
                }
            } else if ($request->has('is_video') && !$hasVideoOptions) {
                Log::warning('Service is marked as video but no video options provided');
            } else if (!$request->has('is_video')) {
                // If service is not a video anymore, delete all video options
                try {
                    $deletedCount = VideoOption::where('service_id', $service->id)->delete();
                    Log::info('Deleted all video options for non-video service', [
                        'service_id' => $service->id,
                        'deleted_count' => $deletedCount
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting video options for non-video service', [
                        'error' => $e->getMessage(),
                        'service_id' => $service->id
                    ]);
                }
            }

            return redirect()
                ->route('admin.services.index')
                ->with('success', 'Service updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update service: ' . $e->getMessage()]);
        }
    }

    public function destroy(Service $service)
    {
        try {
            $service->deleteImage();
            $service->delete();
            return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting service', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error deleting service: ' . $e->getMessage());
        }
    }

    /**
     * تحديث ترتيب الخدمات
     */
    public function updateOrder(Request $request)
    {
        // تسجيل البيانات الواردة للتصحيح
        Log::info('Received services data', [
            'services' => $request->services
        ]);

        $request->validate([
            'services' => 'required|array',
            'services.*.id' => 'required|exists:services,id',
            'services.*.order' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->services as $item) {
                Service::where('id', $item['id'])->update(['sort_order' => $item['order']]);
                // تسجيل كل تحديث للتصحيح
                Log::info('Updated service order', [
                    'id' => $item['id'],
                    'order' => $item['order']
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تحديث ترتيب الخدمات بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating service order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تحديث ترتيب الخدمات: ' . $e->getMessage()], 500);
        }
    }

    private function validateRegexPattern($pattern)
    {
        try {
            // Remove leading and trailing slashes if they exist
            $pattern = trim($pattern, '/');
            
            // Add delimiters and test the pattern
            if (@preg_match('/' . preg_quote($pattern, '/') . '/', '') === false) {
                throw new \Exception(preg_last_error_msg());
            }
        } catch (\Exception $e) {
            throw new \Exception('Invalid regular expression pattern: ' . $e->getMessage());
        }
    }
}
