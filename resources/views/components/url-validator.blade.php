<div x-data="{ 
    urls: [{ url: '', loading: false, error: null, service: null, file_id: null, image_url: null, video_option_id: null, service_id: null, valid: false }],
    totalPoints: 0,

    // Validate URL
    validateUrl(index) {
        if (!this.urls[index].url) return;
        
        this.urls[index].loading = true;
        this.urls[index].error = null;
        this.urls[index].service = null;
        this.urls[index].file_id = null;
        this.urls[index].image_url = null;
        this.urls[index].video_option_id = null;
        
        fetch(`{{ route('validate.url') }}?url=${encodeURIComponent(this.urls[index].url)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.urls[index].loading = false;
                console.log('Server response:', data);
                
                if (data.success) {
                    this.urls[index].valid = true;
                    this.urls[index].service = data.data.service;
                    this.urls[index].file_id = data.data.file_id;
                    this.urls[index].image_url = data.data.image_url;
                    this.urls[index].video_options = data.data.video_options || [];
                    
                    // إذا كان هناك رابط مصحح، قم بتحديثه في حقل الإدخال
                    if (data.data.corrected_url) {
                        console.log('URL corrected from', this.urls[index].url, 'to', data.data.corrected_url);
                        this.urls[index].url = data.data.corrected_url;
                    }
                    
                    // تسجيل معلومات خيارات الفيديو للتصحيح
                    console.log('Service is video?', this.urls[index].service.is_video);
                    console.log('Video options received:', this.urls[index].video_options);
                    
                    // إذا كان هناك خيار واحد فقط للفيديو، قم بتحديده تلقائيًا
                    if (this.urls[index].video_options && this.urls[index].video_options.length === 1) {
                        this.urls[index].video_option_id = this.urls[index].video_options[0].id;
                        console.log('Automatically selected video option:', this.urls[index].video_options[0].id);
                    } else if (this.urls[index].service.is_video && this.urls[index].video_options.length > 0) {
                        // تحديد الخيار الافتراضي (مجاني أو الأول)
                        this.setDefaultVideoOption(index);
                    }
                    
                    this.urlValidated(index, true, data.data);
                    this.calculateTotalPoints();
                } else {
                    this.urls[index].valid = false;
                    this.urls[index].error = data.message;
                    console.log('Validation failed:', data.message);
                    this.urlValidated(index, false, null, data.message);
                }
            })
            .catch(error => {
                console.error('Error validating URL:', error);
                this.urls[index].loading = false;
                this.urls[index].valid = false;
                
                // محاولة الحصول على رسالة الخطأ من الاستجابة إذا كانت متاحة
                if (error.response && error.response.json) {
                    error.response.json().then(data => {
                        this.urls[index].error = data.message || 'An error occurred while validating the URL. Please try again.';
                        console.log('Server error response:', data);
                    }).catch(() => {
                        this.urls[index].error = `Error (${error.status || 'unknown'}): ${error.message || 'An error occurred while validating the URL. Please try again.'}`;
                    });
                } else {
                    this.urls[index].error = `Error: ${error.message || 'An error occurred while validating the URL. Please try again.'}`;
                    console.log('Detailed error:', error);
                }
                
                this.urlValidated(index, false, null, this.urls[index].error);
            });
    },

    addNewUrl() {
        this.urls.push({ url: '', loading: false, error: null, service: null, file_id: null, image_url: null, video_option_id: null, service_id: null, valid: false });
    },

    removeUrl(index) {
        if (this.urls.length > 1) {
            this.urls.splice(index, 1);
            this.calculateTotalPoints();
        }
    },

    calculateTotalPoints() {
        let total = 0;
        
        this.urls.forEach(url => {
            if (url.service && url.valid) {
                // إضافة تكلفة الخدمة الأساسية
                total += parseInt(url.service.points_cost) || 0;
                
                // إضافة تكلفة خيار الفيديو إذا كان محددًا
                if (url.video_option_id && url.video_options) {
                    const selectedOption = url.video_options.find(option => option.id == url.video_option_id);
                    if (selectedOption) {
                        total += parseInt(selectedOption.points_cost) || 0;
                    }
                }
            }
        });
        
        this.totalPoints = total;
        console.log('Total points calculated:', total);
    },
    
    updateVideoOption(index, optionId) {
        this.urls[index].video_option_id = optionId;
        console.log('Video option updated:', optionId);
        
        // تحديث إجمالي النقاط
        this.calculateTotalPoints();
    },
    
    setDefaultVideoOption(index) {
        // البحث عن خيار مجاني أولاً
        const freeOption = this.urls[index].video_options.find(opt => opt.points_cost === 0 || opt.points_cost === '0');
        if (freeOption) {
            this.urls[index].video_option_id = freeOption.id;
            console.log('Selected free video option:', freeOption.id);
        } else {
            // إذا لم يكن هناك خيار مجاني، حدد الخيار الأول
            this.urls[index].video_option_id = this.urls[index].video_options[0].id;
            console.log('Selected first video option:', this.urls[index].video_options[0].id);
        }
        this.calculateTotalPoints();
    },

    urlValidated(index, valid, data, error) {
        // يمكنك إضافة منطق هنا إذا لزم الأمر
    },

    isValid() {
        return this.urls.every(urlObj => urlObj.url && urlObj.service && urlObj.file_id && !urlObj.error && urlObj.valid);
    }
}">
    <div class="space-y-6">
        <!-- URLs Container -->
        <div class="space-y-4">
            <template x-for="(url, index) in urls" :key="index">
                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <!-- URL Input Field -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2">
                            <label :for="'url-' + index" class="block text-sm font-medium text-gray-700">
                                Download URL #<span x-text="index + 1"></span>
                            </label>
                            <button type="button" 
                                    @click="removeUrl(index)"
                                    x-show="urls.length > 1"
                                    class="text-red-600 hover:text-red-800 text-sm">
                                Remove
                            </button>
                        </div>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="url" 
                                   :name="'urls[' + index + '][url]'"
                                   :id="'url-' + index"
                                   x-model="url.url"
                                   @input.debounce.500ms="validateUrl(index)"
                                   class="block w-full pr-10 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-md sm:text-sm"
                                   placeholder="Enter download URL here"
                                   required>
                            
                            <!-- Loading Indicator -->
                            <div x-show="url.loading" 
                                 class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Success Indicator -->
                            <div x-show="url.service && !url.loading" 
                                 class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div x-show="url.error" 
                             x-text="url.error"
                             class="mt-2 text-sm text-red-600"
                             role="alert">
                        </div>

                        <!-- Service Information -->
                        <div x-show="url.service" 
                             class="mt-4 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 p-5 shadow-sm border border-blue-100">
                            <!-- Service Information with Image Preview -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <!-- Image Preview - Left Column -->
                                <div class="md:col-span-1 flex flex-col items-center justify-center">
                                    <template x-if="url.image_url">
                                        <div class="relative w-full h-48 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100 group hover:shadow-md transition-all duration-300">
                                            <img :src="url.image_url" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" :alt="url.service.name">
                                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                                                <p class="text-white text-xs font-medium truncate" x-text="'ID: ' + url.file_id"></p>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!url.image_url">
                                        <div class="w-full h-48 bg-white rounded-lg flex items-center justify-center border border-gray-100 shadow-sm">
                                            <div class="text-center p-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h8a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <p class="text-gray-400 text-sm">No preview available</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Service Details - Right Column -->
                                <div class="md:col-span-2">
                                    <div class="bg-white rounded-lg p-5 shadow-sm border border-gray-100 h-full hover:shadow-md transition-all duration-300">
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="text-lg font-medium text-gray-800" x-text="url.service.name"></h3>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                                                <span x-text="url.service.points_cost"></span> points
                                            </span>
                                        </div>
                                        
                                        <p class="text-sm text-gray-600 mb-4" x-text="url.service.description"></p>
                                        
                                        <div class="flex flex-wrap gap-3 mt-2">
                                            <div class="flex items-center text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span x-text="'File ID: ' + url.file_id"></span>
                                            </div>
                                            
                                            <div x-show="url.service.is_video" class="flex items-center text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-4.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span>Video content</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Video Options Selection -->
                        <div x-show="url.service && url.service.is_video" class="mt-5 bg-white p-5 rounded-lg border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="flex items-center mb-4">
                                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full p-1.5 mr-3 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-800 leading-tight">Video Quality Options</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Select your preferred video quality</p>
                                </div>
                            </div>
                            
                            <div>
                                <template x-if="!url.video_options || url.video_options.length === 0">
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span class="text-sm text-amber-700">No video options available for this service. Please contact technical support.</span>
                                    </div>
                                </template>
                                
                                <div x-show="url.video_options && url.video_options.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="option in url.video_options" :key="option.id">
                                        <div 
                                            class="border rounded-lg overflow-hidden cursor-pointer transition-all duration-300 relative group"
                                            :class="url.video_option_id == option.id ? 
                                                'border-indigo-400 ring-2 ring-indigo-400 shadow-lg bg-indigo-50' : 
                                                'border-gray-200 hover:border-indigo-200 hover:shadow-sm bg-white'"
                                            @click="updateVideoOption(index, option.id)"
                                        >
                                            <!-- Ribbon for selected option -->
                                            <div x-show="url.video_option_id == option.id" class="absolute -left-2 top-4 z-10">
                                                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-bold py-1 px-3 rounded-r-full shadow-md flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Selected
                                                </div>
                                            </div>
                                            
                                            <div class="flex p-4">
                                                <div class="flex-shrink-0 mt-1">
                                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                                         :class="url.video_option_id == option.id ? 'border-indigo-600' : 'border-gray-300 group-hover:border-indigo-300'">
                                                        <div x-show="url.video_option_id == option.id" class="w-2 h-2 rounded-full bg-indigo-600"></div>
                                                    </div>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <label :for="'video-option-' + index + '-' + option.id" class="block text-sm font-medium cursor-pointer"
                                                           :class="url.video_option_id == option.id ? 'text-indigo-900' : 'text-gray-900'">
                                                        <span x-text="option.display_name"></span>
                                                    </label>
                                                    <div class="mt-1 text-xs" 
                                                         :class="url.video_option_id == option.id ? 'text-indigo-700' : 'text-gray-500'"
                                                         x-text="option.parameter_value"></div>
                                                </div>
                                            </div>
                                            
                                            <div class="px-4 py-2 border-t flex justify-between items-center"
                                                 :class="url.video_option_id == option.id ? 'bg-indigo-100 border-indigo-200' : 'bg-gray-50 border-gray-100'">
                                                <div x-show="option.points_cost > 0" 
                                                     :class="url.video_option_id == option.id ? 'text-indigo-800 font-medium' : 'text-indigo-700'"
                                                     class="text-xs">
                                                    <span x-text="option.points_cost"></span> points required
                                                </div>
                                                <div x-show="option.points_cost === 0" 
                                                     :class="url.video_option_id == option.id ? 'text-emerald-700 font-medium' : 'text-emerald-600'"
                                                     class="text-xs">
                                                    Free option
                                                </div>
                                                
                                                <div x-show="url.video_option_id == option.id" 
                                                     class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-medium py-0.5 px-2 rounded shadow-sm">
                                                    Active
                                                </div>
                                            </div>
                                            
                                            <!-- Highlight corner -->
                                            <div x-show="url.video_option_id == option.id" class="absolute top-0 right-0">
                                                <div class="w-16 h-16 bg-gradient-to-bl from-indigo-600 to-transparent transform rotate-45 translate-x-8 -translate-y-8"></div>
                                                <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-sm">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden inputs for form submission -->
                        <input type="hidden" 
                               :name="'urls[' + index + '][service_id]'" 
                               :value="url.service ? url.service.id : ''"
                               x-model="url.service ? url.service.id : ''">
                        
                        <input type="hidden" 
                               :name="'urls[' + index + '][image_url]'" 
                               :value="url.image_url"
                               x-model="url.image_url">

                        <input type="hidden" 
                               :name="'urls[' + index + '][file_id]'" 
                               :value="url.file_id"
                               x-model="url.file_id">

                        <input type="hidden" 
                               :name="'urls[' + index + '][video_option_id]'" 
                               :value="url.video_option_id"
                               x-model="url.video_option_id">
                    </div>
                </div>
            </template>
        </div>

        <!-- Add More Button -->
        <div class="flex justify-center">
            <button type="button" 
                    @click="addNewUrl"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 01-1 1h-3a1 1 0 110-2h3V8a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add another download URL
            </button>
        </div>

        <!-- Total Points Summary -->
        <div x-show="totalPoints > 0" class="mt-6 p-4 bg-gray-50 rounded-lg border">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Total points required:</span>
                <span class="text-lg font-semibold text-gray-900" x-text="totalPoints + ' points'"></span>
            </div>
        </div>
    </div>
</div>
