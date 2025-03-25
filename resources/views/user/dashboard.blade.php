<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- User Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Points Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Points Balance</h3>
                            <div class="p-2 bg-blue-100 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-blue-600">{{ auth()->user()->points }}</p>
                        <p class="text-sm text-gray-500">Available points</p>
                    </div>
                </div>

                <!-- Subscription Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Subscription Status</h3>
                            <div class="p-2 bg-purple-100 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        @if(auth()->user()->subscription_expires_at)
                            @if(auth()->user()->hasActiveSubscription())
                                <p class="mt-4 text-xl font-semibold text-green-600">Active</p>
                                <p class="text-sm text-gray-500">Expires: {{ auth()->user()->subscription_expires_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ auth()->user()->subscription_expires_at->diffForHumans() }}</p>
                            @else
                                <p class="mt-4 text-xl font-semibold text-red-600">Expired</p>
                                <p class="text-sm text-gray-500">Expired on: {{ auth()->user()->subscription_expires_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ auth()->user()->subscription_expires_at->diffForHumans() }}</p>
                            @endif
                        @else
                            <p class="mt-4 text-xl font-semibold text-gray-600">No Active Subscription</p>
                            <p class="text-sm text-gray-500">Contact admin to activate</p>
                        @endif
                    </div>
                </div>

                <!-- Downloads Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-700">Downloads</h3>
                            <div class="p-2 bg-green-100 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-green-600">{{ auth()->user()->downloads->count() }}</p>
                        <p class="text-sm text-gray-500">Total downloads</p>
                        <div class="mt-4">
                            <a href="{{ route('downloads.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                New Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Downloads Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Downloads</h3>
                    
                    @if($recentDownloads->isEmpty())
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            <p class="text-gray-600">You haven't downloaded any files yet.</p>
                            <a href="{{ route('downloads.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Start your first download
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($recentDownloads as $download)
                                <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-200">
                                    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                                        @if($download->image_url)
                                            <div class="w-full h-40 overflow-hidden">
                                                <img src="{{ $download->image_url }}" alt="{{ $download->service->name }}" class="object-cover w-full h-full">
                                            </div>
                                        @else
                                            <div class="w-full h-40 flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-semibold text-gray-900">{{ $download->service->name }}</h4>
                                                <p class="text-sm text-gray-500 mt-1">{{ $download->created_at->format('M d, Y H:i') }}</p>
                                            </div>
                                            <x-status-badge :status="$download->status" />
                                        </div>
                                        
                                        @if($download->videoOption)
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $download->videoOption->display_name }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-4 flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-600">{{ $download->points_spent }} points</span>
                                            
                                            @if($download->status === 'completed' && $download->local_path && isset($download->service))
                                                <a href="{{ route('downloads.download', $download) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-blue-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Download
                                                </a>
                                            @elseif($download->status === 'failed')
                                                <span class="text-xs text-red-600">failed</span>
                                            @else
                                                <span class="text-xs text-gray-500">Processing...</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 text-center">
                            <a href="{{ route('downloads.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                View all downloads
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
