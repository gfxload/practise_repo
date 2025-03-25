<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('New Download') }}
            </h2>
            <a href="{{ route('downloads.index') }}" class="text-blue-600 hover:text-blue-900">
                {{ __(' Back to Downloads') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Points Balance Card -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Points Balance') }}</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Your current points balance:') }}
                                <span class="font-semibold text-lg">{{ Auth::user()->points }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Form Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Add New Download') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Paste your download URL and the service will be automatically detected') }}
                        </p>
                    </div>

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('downloads.store') }}" class="space-y-6">
                        @csrf

                        <x-url-validator />
                        
                        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                            <x-primary-button>
                                {{ __('Start Download') }}
                            </x-primary-button>
                        </div>
<?php /* 
                        <!-- Supported Services -->
                        <div class="mt-6 border-t pt-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('Supported Services') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach(App\Models\Service::active()->get() as $service)
                                    <div class="p-4 border rounded-lg">
                                        @if($service->image_path)
                                            <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="h-8 mb-2">
                                        @endif
                                        <h5 class="font-medium">{{ $service->name }}</h5>
                                        <p class="text-sm text-gray-600">{{ $service->description }}</p>
                                        <p class="text-sm font-bold text-gray-600">{{ $service->points_cost }} points</p>
                                        @if($service->expected_url_format)
                                            <p class="text-xs text-gray-500 mt-1">
                                                Example: {{ $service->expected_url_format }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
*/ ?> 

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
