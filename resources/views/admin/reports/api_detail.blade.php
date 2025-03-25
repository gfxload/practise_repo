<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('تفاصيل طلب API') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('admin.reports.api') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            العودة إلى التقرير
                        </a>
                    </div>
                    
                    <!-- معلومات الطلب -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">معلومات الطلب</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">معرف الطلب:</p>
                                    <p class="font-medium">{{ $apiLog->id }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">الوقت:</p>
                                    <p class="font-medium">{{ $apiLog->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">طريقة API:</p>
                                    <p class="font-medium">
                                        @if($apiLog->method === 'method1')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                الطريقة الأولى
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                                الطريقة الثانية
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">نوع API:</p>
                                    <p class="font-medium">
                                        @if($apiLog->api_type === 'order')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-cyan-100 text-cyan-800">
                                                طلب أمر
                                            </span>
                                        @elseif($apiLog->api_type === 'download')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                طلب تنزيل
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                -
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">الحالة:</p>
                                    <p class="font-medium">
                                        @if($apiLog->status === 'success')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                ناجح
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                فاشل
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">الرابط:</p>
                                    <p class="font-medium break-all">{{ $apiLog->url }}</p>
                                </div>
                                
                                @if($apiLog->order_id)
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">معرف الأمر:</p>
                                    <p class="font-medium">{{ $apiLog->order_id }}</p>
                                </div>
                                @endif
                                
                                @if($apiLog->http_status)
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">حالة HTTP:</p>
                                    <p class="font-medium">{{ $apiLog->http_status }}</p>
                                </div>
                                @endif
                                
                                @if($apiLog->user)
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">المستخدم:</p>
                                    <p class="font-medium">{{ $apiLog->user->name }}</p>
                                </div>
                                @endif
                                
                                @if($apiLog->download)
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">معرف التنزيل:</p>
                                    <p class="font-medium">{{ $apiLog->download->id }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- بيانات الطلب -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">بيانات الطلب</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="mb-2">
                                <button id="toggleRequestBtn" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    عرض/إخفاء البيانات الكاملة
                                </button>
                            </div>
                            <div id="requestDataSummary">
                                <pre class="text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap max-h-40">{{ Str::limit(json_encode($apiLog->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 300) }}</pre>
                            </div>
                            <div id="requestDataFull" class="hidden">
                                <pre class="text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap max-h-96">{{ json_encode($apiLog->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                    
                    <!-- بيانات الاستجابة -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">بيانات الاستجابة</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="mb-2">
                                <button id="toggleResponseBtn" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    عرض/إخفاء البيانات الكاملة
                                </button>
                            </div>
                            <div id="responseDataSummary">
                                <pre class="text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap max-h-40">{{ Str::limit(json_encode($apiLog->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 300) }}</pre>
                            </div>
                            <div id="responseDataFull" class="hidden">
                                <pre class="text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap max-h-96">{{ json_encode($apiLog->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تبديل عرض بيانات الطلب
            const toggleRequestBtn = document.getElementById('toggleRequestBtn');
            const requestDataSummary = document.getElementById('requestDataSummary');
            const requestDataFull = document.getElementById('requestDataFull');
            
            toggleRequestBtn.addEventListener('click', function() {
                requestDataSummary.classList.toggle('hidden');
                requestDataFull.classList.toggle('hidden');
            });
            
            // تبديل عرض بيانات الاستجابة
            const toggleResponseBtn = document.getElementById('toggleResponseBtn');
            const responseDataSummary = document.getElementById('responseDataSummary');
            const responseDataFull = document.getElementById('responseDataFull');
            
            toggleResponseBtn.addEventListener('click', function() {
                responseDataSummary.classList.toggle('hidden');
                responseDataFull.classList.toggle('hidden');
            });
        });
    </script>
    @endpush
</x-app-layout>
