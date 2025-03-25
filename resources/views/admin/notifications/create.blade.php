<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Send Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.notifications.send') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="title" :value="__('Notification Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="message" :value="__('Message')" />
                            <div class="mt-1 rounded-md shadow-sm">
                                <textarea id="message" name="message" rows="8" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>{{ old('message') }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                {{ __('You can use Markdown formatting:') }}
                                <span class="font-mono">**bold**, *italic*, [link](url), # heading</span>
                            </p>
                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('Preview') }}</h3>
                                <div id="preview" class="p-4 bg-gray-50 rounded-md prose max-w-none">
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('message')" />
                        </div>

                        <div>
                            <x-input-label :value="__('Select Recipients')" />
                            <div class="mt-4 space-y-2">
                                <div class="flex items-center">
                                    <input id="all_users" type="checkbox" name="users[]" value="all" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                    <label for="all_users" class="ml-2 block text-sm font-medium text-gray-700">
                                        Send to all users
                                    </label>
                                </div>
                                
                                <div class="mt-4" id="user_list">
                                    <div class="max-h-60 overflow-y-auto space-y-2 p-4 border rounded-md">
                                        @foreach($users as $user)
                                            <div class="flex items-center">
                                                <input id="user_{{ $user->id }}" type="checkbox" name="users[]" value="{{ $user->id }}" 
                                                    class="user-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                                    {{ is_array(old('users')) && in_array($user->id, old('users')) ? 'checked' : '' }}>
                                                <label for="user_{{ $user->id }}" class="ml-2 block text-sm font-medium text-gray-700">
                                                    {{ $user->name }} ({{ $user->email }})
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('users')" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Send Notifications') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allUsersCheckbox = document.getElementById('all_users');
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            const userList = document.getElementById('user_list');
            const messageInput = document.getElementById('message');
            const preview = document.getElementById('preview');

            // Function to update the form state
            function updateFormState() {
                const isAllSelected = allUsersCheckbox.checked;
                userList.style.display = isAllSelected ? 'none' : 'block';
                
                if (isAllSelected) {
                    userCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                }
            }

            // Initialize form state
            updateFormState();

            // Handle "Send to all users" checkbox
            allUsersCheckbox.addEventListener('change', updateFormState);

            // Handle individual user checkboxes
            userCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        allUsersCheckbox.checked = false;
                        userList.style.display = 'block';
                    }
                });
            });

            // Ensure at least one option is selected before form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                const isAllSelected = allUsersCheckbox.checked;
                const hasIndividualSelection = Array.from(userCheckboxes).some(cb => cb.checked);

                if (!isAllSelected && !hasIndividualSelection) {
                    e.preventDefault();
                    alert('Please select at least one recipient');
                }
            });

            // Live Markdown preview
            function updatePreview() {
                const markdown = messageInput.value;
                preview.innerHTML = marked.parse(markdown);
            }

            messageInput.addEventListener('input', updatePreview);
            updatePreview(); // Initial preview
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .prose {
            max-width: none;
        }
        .prose img {
            margin: 0 auto;
        }
        .prose :last-child {
            margin-bottom: 0;
        }
    </style>
    @endpush
</x-app-layout>
