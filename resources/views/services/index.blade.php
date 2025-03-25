<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Available Services
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Header Section with Summary -->
                    <div class="mb-8 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-100 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800">All Available Services</h3>
                                <p class="mt-2 text-gray-600">Browse our collection of services and their pricing details</p>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="h-6 w-6 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18m-7 5h7" />
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">Total Services</div>
                                            <div class="text-2xl font-bold text-indigo-600">{{ $services->count() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($services->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No services available at the moment.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Services Table -->
                        <div class="overflow-x-auto rounded-lg shadow-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gradient-to-r from-indigo-600 to-purple-600">
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                            Service
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                            Points
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                            Video Options
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($services as $service)
                                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} {{ $service->is_active ? '' : 'opacity-50' }} hover:bg-gray-100 transition-colors duration-200">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-12 w-12">
                                                        @if($service->image_path)
                                                            <img class="h-12 w-12 rounded-lg object-cover" src="{{ $service->image_url }}" alt="{{ $service->name }}">
                                                        @else
                                                            <div class="h-12 w-12 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $service->name }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            Cost: {{ $service->points_cost }} Points
                                                        </div>
                                                        @if($service->is_video)
                                                            <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                                Video Content
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 line-clamp-2">
                                                    {{ $service->description }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-indigo-500 to-purple-500 text-white">
                                                    {{ $service->points_cost }} Points
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($service->is_video && $service->videoOptions->count() > 0)
                                                    <div class="space-y-2">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $service->videoOptions->count() }} Options Available
                                                        </div>
                                                        <div class="bg-gray-50 rounded-md p-3 border border-gray-200 max-h-40 overflow-y-auto">
                                                            @foreach($service->videoOptions as $option)
                                                                <div class="mb-2 pb-2 @if(!$loop->last) border-b border-gray-200 @endif">
                                                                    <div class="flex justify-between items-center">
                                                                        <span class="font-medium text-sm">{{ $option->display_name }}</span>
                                                                        @if($option->points_cost > 0)
                                                                            <span class="text-xs bg-indigo-100 text-indigo-800 px-1.5 py-0.5 rounded">
                                                                                +{{ $option->points_cost }} pts
                                                                            </span>
                                                                        @else
                                                                            <span class="text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded">
                                                                                Free
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-500 italic">
                                                        No options available
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($service->is_active)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="mr-1 h-3 w-3 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                                            <circle cx="4" cy="4" r="3" />
                                                        </svg>
                                                        Available
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        <svg class="mr-1 h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 8 8">
                                                            <circle cx="4" cy="4" r="3" />
                                                        </svg>
                                                        Unavailable
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>