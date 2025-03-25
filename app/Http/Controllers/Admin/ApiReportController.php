<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\Download;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApiReportController extends Controller
{
    public function index(Request $request)
    {
        // بناء استعلام قاعدة البيانات
        $query = ApiLog::query();
        
        // تصفية البيانات حسب الطريقة
        $method = $request->get('method', 'all');
        if ($method !== 'all') {
            $query->where('method', $method);
        }
        
        // تصفية بيانات الطريقة الثانية
        $method2Type = $request->get('method2_type', 'all');
        if ($method === 'method2' && $method2Type !== 'all') {
            $query->where('api_type', $method2Type);
        }
        
        // تنفيذ الاستعلام
        $logData = $query->orderBy('created_at', 'desc')
                         ->with(['user', 'download'])
                         ->paginate(50);
        
        // إحصائيات عامة
        $stats = [
            'total_requests' => ApiLog::count(),
            'method1_count' => ApiLog::where('method', 'method1')->count(),
            'method2_count' => ApiLog::where('method', 'method2')->count(),
            'order_requests' => ApiLog::where('api_type', 'order')->count(),
            'download_requests' => ApiLog::where('api_type', 'download')->count(),
            'successful_requests' => ApiLog::where('status', 'success')->count(),
            'failed_requests' => ApiLog::where('status', 'failed')->count(),
        ];
        
        // تجميع البيانات حسب التاريخ
        $requestsByDate = ApiLog::selectRaw('DATE(created_at) as date, 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN method = "method1" THEN 1 ELSE 0 END) as method1,
                                    SUM(CASE WHEN method = "method2" THEN 1 ELSE 0 END) as method2,
                                    SUM(CASE WHEN api_type = "order" THEN 1 ELSE 0 END) as order_count,
                                    SUM(CASE WHEN api_type = "download" THEN 1 ELSE 0 END) as download_count,
                                    SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success,
                                    SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
                            ->groupBy(DB::raw('DATE(created_at)'))
                            ->orderBy('date', 'desc')
                            ->limit(30)
                            ->get()
                            ->map(function($item) {
                                return [
                                    'date' => $item->date,
                                    'total' => $item->total,
                                    'method1' => $item->method1,
                                    'method2' => $item->method2,
                                    'order' => $item->order_count,
                                    'download' => $item->download_count,
                                    'success' => $item->success,
                                    'failed' => $item->failed
                                ];
                            })
                            ->toArray();
        
        // ترتيب البيانات تصاعدياً للرسم البياني
        $chartRequestsByDate = array_reverse($requestsByDate);
        
        // بيانات الرسم البياني
        $chartData = [
            'labels' => array_column($chartRequestsByDate, 'date'),
            'method1' => array_column($chartRequestsByDate, 'method1'),
            'method2' => array_column($chartRequestsByDate, 'method2'),
            'success' => array_column($chartRequestsByDate, 'success'),
            'failed' => array_column($chartRequestsByDate, 'failed'),
        ];
        
        return view('admin.reports.api', compact('logData', 'stats', 'requestsByDate', 'method', 'method2Type', 'chartData'));
    }
    
    public function detail(Request $request, $id)
    {
        // البحث عن السجل المطلوب
        $apiLog = ApiLog::with(['user', 'download'])->findOrFail($id);
        
        // الحصول على معلومات إضافية
        $relatedLogs = null;
        
        // إذا كان هناك order_id، ابحث عن السجلات المرتبطة
        if ($apiLog->order_id) {
            $relatedLogs = ApiLog::where('order_id', $apiLog->order_id)
                                ->where('id', '!=', $apiLog->id)
                                ->orderBy('created_at')
                                ->get();
        }
        
        return view('admin.reports.api_detail', compact('apiLog', 'relatedLogs'));
    }
}
