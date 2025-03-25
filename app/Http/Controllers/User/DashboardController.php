<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $recentDownloads = $user->downloads()
            ->with(['service', 'videoOption'])
            ->latest()
            ->take(6)
            ->get();

        return view('user.dashboard', compact('recentDownloads'));
    }
}
