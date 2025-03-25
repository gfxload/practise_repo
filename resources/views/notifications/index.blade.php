<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($notifications->isEmpty())
                        <p class="text-gray-500 text-center py-4">{{ __('No notifications yet.') }}</p>
                    @else
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="p-4 rounded-lg {{ $notification->is_read ? 'bg-gray-50' : 'bg-blue-50 border-l-4 border-blue-500' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $notification->title }}</h3>
                                            <div class="mt-2 prose max-w-none">
                                                {!! Str::markdown($notification->message) !!}
                                            </div>
                                            <div class="mt-2 text-sm text-gray-500">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        @if(!$notification->is_read)
                                            <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800">
                                                    {{ __('Mark as read') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>

                        @if($notifications->where('is_read', false)->count() > 0)
                            <div class="mt-6 text-right">
                                <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                                    @csrf
                                    <x-primary-button>
                                        {{ __('Mark all as read') }}
                                    </x-primary-button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
