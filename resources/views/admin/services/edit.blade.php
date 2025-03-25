<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Service') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Service Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ old('description', $service->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="points_cost" :value="__('Points Cost')" />
                            <x-text-input id="points_cost" name="points_cost" type="number" min="0" class="mt-1 block w-full" :value="old('points_cost', $service->points_cost)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('points_cost')" />
                        </div>

                        <div>
                            <x-input-label for="url_pattern" :value="__('URL Pattern (Regex)')" />
                            <x-text-input id="url_pattern" name="url_pattern" type="text" class="mt-1 block w-full" :value="old('url_pattern', $service->url_pattern)" placeholder="https?:\/\/([^\/]+)" />
                            <p class="mt-1 text-sm text-gray-500">{{ __('Enter the pattern without delimiters (no leading/trailing /). Example: https?:\/\/([^\/]+) for matching http or https URLs.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('url_pattern')" />
                        </div>

                        <div>
                            <x-input-label for="file_id_pattern" :value="__('File ID Pattern (Regex)')" />
                            <x-text-input id="file_id_pattern" name="file_id_pattern" type="text" class="mt-1 block w-full" :value="old('file_id_pattern', $service->file_id_pattern)" placeholder="\/file\/([a-zA-Z0-9]+)" />
                            <p class="mt-1 text-sm text-gray-500">{{ __('Enter the pattern without delimiters. Use capturing groups () to specify the ID part. Example: \/file\/([a-zA-Z0-9]+)') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('file_id_pattern')" />
                        </div>

                        <div>
                            <x-input-label for="expected_url_format" :value="__('Expected URL Format')" />
                            <x-text-input id="expected_url_format" name="expected_url_format" type="text" class="mt-1 block w-full" :value="old('expected_url_format', $service->expected_url_format)" placeholder="https://example.com/file/abc123" />
                            <p class="mt-1 text-sm text-gray-500">{{ __('Example of the expected URL format to help users.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('expected_url_format')" />
                        </div>

                        <div>
                            <x-input-label for="image" :value="__('Service Image')" />
                            @if($service->image_path)
                                <div class="mt-2 mb-4">
                                    <img src="{{ Storage::url($service->image_path) }}" alt="{{ $service->name }}" class="w-32 h-32 object-cover rounded-lg">
                                </div>
                            @endif
                            <input type="file" id="image" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100
                            "/>
                            <p class="mt-1 text-sm text-gray-500">
                                PNG, JPG, GIF up to 2MB. Leave empty to keep the current image.
                            </p>
                            <x-input-error class="mt-2" :messages="$errors->get('image')" />
                        </div>

                        <div class="flex items-center">
                            <input id="is_active" name="is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ $service->is_active ? 'checked' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>

                        <div class="flex items-center">
                            <input id="is_video" name="is_video" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" {{ $service->is_video ? 'checked' : '' }}>
                            <label for="is_video" class="ml-2 block text-sm text-gray-900">This service is for videos</label>
                        </div>

                        <!-- Video Options Section -->
                        <div id="video-options-section" class="{{ $service->is_video ? '' : 'hidden' }} space-y-6 border p-4 rounded-lg bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Video Options</h3>
                            <p class="text-sm text-gray-500">Add options for video quality or format that will be shown to users.</p>
                            
                            <div class="bg-yellow-50 p-3 rounded-md mb-4 border border-yellow-200">
                                <p class="text-sm text-yellow-800">
                                    <strong>Important:</strong> You must add at least one video option by clicking the "Add Option" button below.
                                    Without video options, users won't be able to select video quality when downloading.
                                </p>
                            </div>
                            
                            <div id="video-options-container">
                                <!-- Existing video options will be loaded here -->
                                @foreach($service->videoOptions as $index => $option)
                                <div class="video-option border p-3 rounded-md mb-4 bg-white">
                                    <div class="flex justify-end">
                                        <button type="button" class="remove-option text-red-500 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    <input type="hidden" name="video_options[{{ $index }}][id]" value="{{ $option->id }}">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Parameter Name</label>
                                            <input type="text" name="video_options[{{ $index }}][parameter_name]" value="{{ $option->parameter_name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. gfxsite" required>
                                            <p class="mt-1 text-xs text-gray-500">The parameter name to send with the API request</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Parameter Value</label>
                                            <input type="text" name="video_options[{{ $index }}][parameter_value]" value="{{ $option->parameter_value }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. adobestock_vhd" required>
                                            <p class="mt-1 text-xs text-gray-500">The value to send with the parameter</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Display Name</label>
                                            <input type="text" name="video_options[{{ $index }}][display_name]" value="{{ $option->display_name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. HD Quality" required>
                                            <p class="mt-1 text-xs text-gray-500">The name shown to users</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Additional Points Cost</label>
                                            <input type="number" name="video_options[{{ $index }}][points_cost]" value="{{ $option->points_cost }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. 10" required>
                                            <p class="mt-1 text-xs text-gray-500">Extra points cost for this option</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            @error('video_options')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            
                            <button type="button" id="add-video-option" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Add Option
                            </button>
                        </div>

                        <div class="flex items-center justify-end">
                            <a href="{{ route('admin.services.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <x-primary-button class="ml-4">
                                {{ __('Update Service') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Template for video option -->
<template id="video-option-template">
    <div class="video-option border p-3 rounded-md mb-4 bg-white">
        <div class="flex justify-end">
            <button type="button" class="remove-option text-red-500 hover:text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Parameter Name</label>
                <input type="text" name="video_options[INDEX][parameter_name]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. gfxsite" required>
                <p class="mt-1 text-xs text-gray-500">The parameter name to send with the API request</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Parameter Value</label>
                <input type="text" name="video_options[INDEX][parameter_value]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. adobestock_vhd" required>
                <p class="mt-1 text-xs text-gray-500">The value to send with the parameter</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Display Name</label>
                <input type="text" name="video_options[INDEX][display_name]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. HD Quality" required>
                <p class="mt-1 text-xs text-gray-500">The name shown to users</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Additional Points Cost</label>
                <input type="number" name="video_options[INDEX][points_cost]" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. 10" required>
                <p class="mt-1 text-xs text-gray-500">Extra points cost for this option</p>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isVideoCheckbox = document.getElementById('is_video');
        const videoOptionsSection = document.getElementById('video-options-section');
        const addVideoOptionBtn = document.getElementById('add-video-option');
        const videoOptionsContainer = document.getElementById('video-options-container');
        const videoOptionTemplate = document.getElementById('video-option-template');
        
        let optionIndex = {{ count($service->videoOptions) }};
        
        // Function to add a new video option
        function addVideoOption(defaultValues = {}) {
            const newOption = videoOptionTemplate.content.cloneNode(true);
            
            // Update the index for form fields
            const inputs = newOption.querySelectorAll('input[name*="INDEX"]');
            inputs.forEach(input => {
                const newName = input.name.replace('INDEX', optionIndex);
                input.name = newName;
                input.id = newName; // Add ID for better accessibility
                
                // Set default values if provided
                const fieldName = input.name.match(/\[([^\]]+)\]$/)[1];
                if (defaultValues[fieldName]) {
                    input.value = defaultValues[fieldName];
                }
            });
            
            // Add remove button functionality
            const removeBtn = newOption.querySelector('.remove-option');
            removeBtn.addEventListener('click', function() {
                this.closest('.video-option').remove();
            });
            
            videoOptionsContainer.appendChild(newOption);
            optionIndex++;
        }
        
        // Toggle video options section
        isVideoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                videoOptionsSection.classList.remove('hidden');
                
                // If no video options exist yet, add one automatically with default values
                if (videoOptionsContainer.children.length === 0) {
                    addVideoOption({
                        'parameter_name': 'gfxsite',
                        'parameter_value': 'adobestock_vhd',
                        'display_name': 'HD Quality (Default)',
                        'points_cost': '0'
                    });
                }
            } else {
                videoOptionsSection.classList.add('hidden');
                // Clear all options when unchecking is_video
                videoOptionsContainer.innerHTML = '';
            }
        });
        
        // Add new video option button click handler
        addVideoOptionBtn.addEventListener('click', function() {
            addVideoOption();
        });
        
        // Add form submit event listener to ensure video options are properly submitted
        document.querySelector('form').addEventListener('submit', function(e) {
            if (isVideoCheckbox.checked && videoOptionsContainer.children.length === 0) {
                e.preventDefault();
                alert('You must add at least one video option for video services.');
                return false;
            }
        });
        
        // Initialize the video options section based on the checkbox state
        if (isVideoCheckbox.checked) {
            videoOptionsSection.classList.remove('hidden');
        }
    });
</script>
