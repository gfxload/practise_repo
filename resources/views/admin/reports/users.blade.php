<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Filters -->
                <form method="GET" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="search" :value="__('Search')" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" 
                                :value="request('search')" placeholder="Name or Email"/>
                        </div>
                        <div>
                            <x-input-label for="min_downloads" :value="__('Min Downloads')" />
                            <x-text-input id="min_downloads" name="min_downloads" type="number" class="mt-1 block w-full" 
                                :value="request('min_downloads')" min="0"/>
                        </div>
                        <div>
                            <x-input-label for="min_points" :value="__('Min Points Spent')" />
                            <x-text-input id="min_points" name="min_points" type="number" class="mt-1 block w-full" 
                                :value="request('min_points')" min="0"/>
                        </div>
                        <div class="flex items-end">
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                            @if(request()->hasAny(['search', 'min_downloads', 'min_points']))
                                <a href="{{ route('admin.reports.users') }}" class="ml-3">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                        class="flex items-center">
                                        User
                                        @if(request('sort') === 'name')
                                            <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'downloads_count', 'direction' => request('sort') === 'downloads_count' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                        class="flex items-center">
                                        Downloads
                                        @if(request('sort') === 'downloads_count')
                                            <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total_points_spent', 'direction' => request('sort') === 'total_points_spent' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                        class="flex items-center">
                                        Points Spent
                                        @if(request('sort') === 'total_points_spent')
                                            <span class="ml-1">{{ request('direction') === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->downloads_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $user->total_points_spent }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>