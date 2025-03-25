<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Subscription') }} - {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.subscriptions.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Subscription Expiry -->
                            <div>
                                <x-input-label for="subscription_expires_at" :value="__('Subscription Expires At')" />
                                <x-text-input id="subscription_expires_at" name="subscription_expires_at" type="date" 
                                    class="mt-1 block w-full"
                                    :value="old('subscription_expires_at', $user->subscription_expires_at?->format('Y-m-d'))"
                                    required />
                                <x-input-error :messages="$errors->get('subscription_expires_at')" class="mt-2" />
                            </div>

                            <!-- Points -->
                            <div>
                                <x-input-label for="points" :value="__('Current Points')" />
                                <x-text-input id="points" name="points" type="number" 
                                    class="mt-1 block w-full"
                                    :value="old('points', $user->points)"
                                    required />
                                <x-input-error :messages="$errors->get('points')" class="mt-2" />
                            </div>

                            <!-- Points to Rollover -->
                            <div>
                                <x-input-label for="points_to_rollover" :value="__('Points to Rollover')" />
                                <x-text-input id="points_to_rollover" name="points_to_rollover" type="number" 
                                    class="mt-1 block w-full"
                                    :value="old('points_to_rollover', $user->points_to_rollover)"
                                    required />
                                <x-input-error :messages="$errors->get('points_to_rollover')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-4">
                            <x-secondary-button onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Subscription History -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Subscription History') }}</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($user->activities as $activity)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @foreach($activity->properties['attributes'] ?? [] as $attribute => $value)
                                                @if(isset($activity->properties['old'][$attribute]))
                                                    <div>
                                                        <span class="font-medium">{{ Str::title(str_replace('_', ' ', $attribute)) }}:</span>
                                                        {{ $activity->properties['old'][$attribute] }} →
                                                        {{ $value }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->causer?->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
