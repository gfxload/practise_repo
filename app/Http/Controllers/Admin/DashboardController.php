<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $activeDownloads = Download::whereIn('status', ['pending', 'processing'])->count();
        $activeServices = Service::active()->count();
        $recentDownloads = Download::with(['user', 'service'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeDownloads',
            'activeServices',
            'recentDownloads'
        ));
    }
}
