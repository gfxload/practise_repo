<x-app-layout>
    <x-slot name="header"> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Services Management') }}
            </h2>
            <div class="flex space-x-6"> <!-- تقليل التلاصق -->
                <button id="saveOrderBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Save Order
                </button>
                <a href="{{ route('admin.services.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    New Service
                </a>
            </div>
        </div>
    </x-slot>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- عنصر الإشعارات -->
            <div id="notification-container" style="display: none;" class="mb-4"></div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- رسالة تعليمات الترتيب -->
                    <div id="reorderInstructions" class="mb-4 p-4 bg-blue-50 text-blue-700 rounded-md border border-blue-200">
                        <p>You can reorder services by dragging and dropping rows. Click "Save Order" when you're done.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points Cost</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="servicesTableBody" class="bg-white divide-y divide-gray-200">
                                @foreach($services as $service)
                                <tr data-id="{{ $service->id }}" data-order="{{ $service->sort_order }}" class="service-row">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="handle cursor-move mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="text-gray-400" viewBox="0 0 16 16">
                                                    <path d="M7 2a1 1 0 011 1v1h3a1 1 0 110 2H9.20l-1 1-1-1H5a1 1 0 110-2h3V3a1 1 0 011-1zm0 5a1 1 0 011 1v1h8a1 1 0 110 2h-8v1a1 1 0 11-2 0v-1H2a1 1 0 110-2h4V8a1 1 0 011-1zm0 5a1 1 0 011 1v1h3a1 1 0 110 2H9.20l-1 1-1-1H5a1 1 0 110-2h3v-1a1 1 0 011-1z"/>
                                                </svg>
                                            </span>
                                            <span class="sort-order">{{ $service->sort_order }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $service->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ Str::limit($service->description, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $service->points_cost }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $service->is_video ? 'Yes' : 'No' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-status-badge :status="$service->is_active ? 'active' : 'inactive'" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.services.edit', $service) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $services->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- تضمين jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- تضمين jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .notification-success {
            background-color: #10b981;
        }
        
        .notification-error {
            background-color: #ef4444;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <script>
        $(document).ready(function() {
            // وظيفة لعرض الإشعارات
            function showNotification(type, message) {
                var notificationClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                var notificationIcon = type === 'success' ? '✓' : '✗';
                
                // تحديث محتوى الإشعار
                $("#notification-container")
                    .attr('class', 'mb-4 p-4 border rounded ' + notificationClass)
                    .html('<strong>' + notificationIcon + ' ' + (type === 'success' ? 'نجاح!' : 'خطأ!') + '</strong> ' + message)
                    .fadeIn();
                
                // إخفاء الإشعار بعد 3 ثوان
                setTimeout(function() {
                    $("#notification-container").fadeOut();
                }, 3000);
            }
            
            // تهيئة السحب والإفلات باستخدام jQuery UI
            $("#servicesTableBody").sortable({
                handle: ".handle",
                axis: "y",
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function(event, ui) {
                    // تحديث أرقام الترتيب
                    updateOrderNumbers();
                }
            });
            
            // تحديث أرقام الترتيب
            function updateOrderNumbers() {
                $(".service-row").each(function(index) {
                    var newOrder = index + 1;
                    $(this).attr("data-order", newOrder);
                    $(this).find(".sort-order").text(newOrder);
                });
            }
            
            // حفظ الترتيب
            $("#saveOrderBtn").click(function() {
                // إظهار مؤشر التحميل
                var loadingOverlay = $('<div class="loading-overlay"><div class="loading-spinner"></div></div>');
                $("body").append(loadingOverlay);
                
                var services = [];
                
                $(".service-row").each(function(index) {
                    services.push({
                        id: $(this).attr("data-id"),
                        order: index + 1
                    });
                });
                
                console.log("Sending data:", services);
                
                // إرسال البيانات إلى الخادم
                $.ajax({
                    url: "{{ route('admin.services.update-order') }}",
                    method: "POST",
                    data: { 
                        services: services, 
                        _token: "{{ csrf_token() }}" 
                    },
                    success: function(response) {
                        // إزالة مؤشر التحميل
                        loadingOverlay.remove();
                        
                        console.log("Response:", response);
                        if (response.success) {
                            // تحديث قيم الترتيب في الصفحة
                            $(".service-row").each(function(index) {
                                $(this).attr("data-order", index + 1);
                                $(this).find(".sort-order").text(index + 1);
                            });
                            
                            // عرض رسالة نجاح
                            showNotification('success', response.message || 'تم تحديث ترتيب الخدمات بنجاح');
                        } else {
                            // عرض رسالة خطأ
                            showNotification('error', response.message || 'حدث خطأ أثناء تحديث ترتيب الخدمات');
                        }
                    },
                    error: function(xhr, status, error) {
                        // إزالة مؤشر التحميل
                        loadingOverlay.remove();
                        
                        console.error("Error:", error);
                        console.error("Response:", xhr.responseText);
                        
                        // عرض رسالة خطأ
                        showNotification('error', 'حدث خطأ أثناء الاتصال بالخادم');
                    }
                });
            });
        });
    </script>
</x-app-layout>
