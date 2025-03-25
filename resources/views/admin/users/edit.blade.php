<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('New Password (leave blank to keep current)')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                        </div>

                        <div>
                            <x-input-label for="points" :value="__('Points')" />
                            <x-text-input id="points" name="points" type="number" min="0" class="mt-1 block w-full" :value="old('points', $user->points)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('points')" />
                        </div>

                        <div>
                            <x-input-label for="subscription_expires_at" :value="__('Subscription Expiry Date')" />
                            <x-text-input id="subscription_expires_at" name="subscription_expires_at" type="datetime-local" class="mt-1 block w-full" :value="old('subscription_expires_at', $user->subscription_expires_at ? date('Y-m-d\TH:i', strtotime($user->subscription_expires_at)) : '')" />
                            <x-input-error class="mt-2" :messages="$errors->get('subscription_expires_at')" />
                        </div>

                        <div class="flex items-center space-x-6">
                            <div class="flex items-center">
                                <input id="is_admin" name="is_admin" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ $user->is_admin ? 'checked' : '' }}>
                                <label for="is_admin" class="ml-2 block text-sm text-gray-900">Admin</label>
                            </div>

                            <div class="flex items-center">
                                <input id="is_active" name="is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ $user->is_active ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button class="ml-4">
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if ($user->id !== auth()->id())
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="flex justify-end">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                                onclick="return confirm('Are you sure you want to delete this user?')">
                                {{ __('Delete User') }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
