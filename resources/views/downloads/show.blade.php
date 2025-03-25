<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Download Details') }}
            </h2>
            <a href="{{ route('downloads.index') }}" class="text-blue-600 hover:text-blue-900">
                {{ __('← Back to Downloads') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Download Information') }}</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Service') }}</label>
                                    <p class="mt-1">{{ $download->service->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('File ID') }}</label>
                                    <p class="mt-1">{{ $download->file_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Points Spent') }}</label>
                                    <p class="mt-1">{{ $download->points_spent }}</p>
                                </div>

                                @if($download->service->is_video && $download->videoOption)
                                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Video Quality</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        {{ $download->videoOption->display_name }}
                                        @if($download->videoOption->points_cost > 0)
                                            <span class="text-gray-500">(+{{ $download->videoOption->points_cost }} points)</span>
                                        @else
                                            <span class="text-green-500">(Free)</span>
                                        @endif
                                    </dd>
                                </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($download->status === 'completed') bg-green-100 text-green-800
                                            @elseif($download->status === 'failed') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($download->status) }}
                                        </span>
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('URL') }}</label>
                                    <p class="mt-1 break-all">{{ $download->original_url }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Created At') }}</label>
                                    <p class="mt-1">{{ $download->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>

                                @if($download->completed_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Completed At') }}</label>
                                        <p class="mt-1">{{ $download->completed_at->format('Y-m-d H:i:s') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($download->status === 'completed')
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Download Link') }}</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-2">{{ __('Your download is ready. Click the button below to start downloading.') }}</p>
                                    <a href="{{ route('downloads.download', $download) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        {{ __('Download File') }}
                                    </a>
                                </div>
                            </div>
                        @elseif($download->status === 'failed')
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Error Information') }}</h3>
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <p class="text-sm text-red-600">{{ $download->error_message ?? __('An error occurred during the download process.') }}</p>
                                </div>
                            </div>
                        @else
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Processing') }}</h3>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <p class="text-sm text-yellow-600">{{ __('Your download is being processed. Please check back later.') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
