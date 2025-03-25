<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function downloadSettings()
    {
        $downloadMethod = Setting::get('download_method', 'method1');
        Log::info('Current download method:', ['method' => $downloadMethod]);
        
        return view('admin.settings.download', compact('downloadMethod'));
    }

    public function updateDownloadSettings(Request $request)
    {
        $request->validate([
            'download_method' => 'required|in:method1,method2',
        ]);

        Log::info('Updating download method:', ['method' => $request->download_method]);

        try {
            $setting = Setting::set('download_method', $request->download_method);
            Log::info('Setting updated successfully:', ['setting' => $setting]);
            return back()->with('success', 'تم تحديث إعدادات التحميل بنجاح');
        } catch (\Exception $e) {
            Log::error('Failed to update setting:', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء حفظ الإعدادات');
        }
    }
}
