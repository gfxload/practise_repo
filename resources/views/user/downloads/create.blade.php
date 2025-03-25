<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Download') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('downloads.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="url" :value="__('URL to Download')" />
                            <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('url')" />
                        </div>

                        <div>
                            <h3 class="text-lg font-medium mb-4">Available Services</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($services as $service)
                                <div class="border rounded-lg p-4">
                                    <label class="flex items-start space-x-3">
                                        <input type="radio" name="service_id" value="{{ $service->id }}" class="mt-1" required>
                                        <div class="flex-1">
                                            <div class="font-medium">{{ $service->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $service->description }}</div>
                                            <div class="text-sm font-medium text-blue-600 mt-1">{{ $service->points_cost }} points</div>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Your current balance: <span class="font-medium">{{ auth()->user()->points }} points</span>
                            </div>
                            <x-primary-button>
                                {{ __('Start Download') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
