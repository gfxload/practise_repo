<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * عرض قائمة بجميع الخدمات المتاحة
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $services = Service::active()->with('videoOptions')->orderBy('sort_order')->get();
        return view('services.index', compact('services'));
    }

    /**
     * عرض تفاصيل خدمة محددة
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function show(Service $service)
    {
        $service->load('videoOptions');
        return view('services.show', compact('service'));
    }
}

