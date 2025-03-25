<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Get total users count
        $totalUsers = User::count();
        
        // Get total downloads count
        $totalDownloads = Download::count();
        
        // Get downloads count by service
        $downloadsByService = Download::select('services.name', DB::raw('count(*) as total'))
            ->join('services', 'downloads.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name')
            ->get();
            
        // Get total points spent
        $totalPointsSpent = Download::sum('points_spent');
        
        // Get most active users (top 5)
        $mostActiveUsers = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.points',
            'users.subscription_expires_at',
            'users.created_at',
            'users.updated_at',
            DB::raw('count(downloads.id) as downloads_count')
        ])
            ->leftJoin('downloads', 'users.id', '=', 'downloads.user_id')
            ->groupBy([
                'users.id',
                'users.name',
                'users.email',
                'users.points',
                'users.subscription_expires_at',
                'users.created_at',
                'users.updated_at'
            ])
            ->orderByDesc('downloads_count')
            ->limit(5)
            ->get();
            
        // Get downloads count by status
        $downloadsByStatus = Download::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return view('admin.reports.index', compact(
            'totalUsers',
            'totalDownloads',
            'downloadsByService',
            'totalPointsSpent',
            'mostActiveUsers',
            'downloadsByStatus'
        ));
    }

    public function users(Request $request)
    {
        $query = User::query()
            ->select('users.*', DB::raw('count(downloads.id) as downloads_count'), DB::raw('sum(downloads.points_spent) as total_points_spent'))
            ->leftJoin('downloads', 'users.id', '=', 'downloads.user_id')
            ->groupBy('users.id');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('min_downloads')) {
            $query->having('downloads_count', '>=', $request->min_downloads);
        }

        if ($request->filled('min_points')) {
            $query->having('total_points_spent', '>=', $request->min_points);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'downloads_count');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(15)->withQueryString();

        return view('admin.reports.users', compact('users'));
    }

    public function downloads(Request $request)
    {
        $query = Download::query()
            ->with(['user', 'service']);

        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('service')) {
            $query->where('service_id', $request->service);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $downloads = $query->paginate(15)->withQueryString();
        $services = Service::all();

        return view('admin.reports.downloads', compact('downloads', 'services'));
    }

    public function services(Request $request)
    {
        $query = Service::query()
            ->select('services.*', 
                DB::raw('count(downloads.id) as downloads_count'),
                DB::raw('sum(downloads.points_spent) as total_points_spent'),
                DB::raw('count(distinct downloads.user_id) as unique_users')
            )
            ->leftJoin('downloads', 'services.id', '=', 'downloads.service_id')
            ->groupBy('services.id');

        // Apply filters
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('min_downloads')) {
            $query->having('downloads_count', '>=', $request->min_downloads);
        }

        if ($request->filled('min_points')) {
            $query->having('total_points_spent', '>=', $request->min_points);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'downloads_count');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $services = $query->paginate(15)->withQueryString();

        return view('admin.reports.services', compact('services'));
    }
}
