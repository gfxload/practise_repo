<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('تقرير استخدام API') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- فلاتر البحث -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">فلاتر البحث</h3>
                        <form action="{{ route('admin.reports.api') }}" method="GET" class="flex flex-wrap gap-4">
                            <div class="flex-1">
                                <label for="method" class="block text-sm font-medium text-gray-700 mb-1">طريقة التنزيل</label>
                                <select id="method" name="method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="all" {{ $method === 'all' ? 'selected' : '' }}>جميع الطرق</option>
                                    <option value="method1" {{ $method === 'method1' ? 'selected' : '' }}>الطريقة الأولى</option>
                                    <option value="method2" {{ $method === 'method2' ? 'selected' : '' }}>الطريقة الثانية</option>
                                </select>
                            </div>
                            
                            <div class="flex-1" id="method2TypeContainer" style="{{ $method !== 'method2' ? 'display: none;' : '' }}">
                                <label for="method2_type" class="block text-sm font-medium text-gray-700 mb-1">نوع طلب الطريقة الثانية</label>
                                <select id="method2_type" name="method2_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="all" {{ $method2Type === 'all' ? 'selected' : '' }}>جميع الأنواع</option>
                                    <option value="order" {{ $method2Type === 'order' ? 'selected' : '' }}>طلب (Order)</option>
                                    <option value="download" {{ $method2Type === 'download' ? 'selected' : '' }}>تنزيل (Download)</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    تطبيق الفلاتر
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- إحصائيات عامة -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-800">إجمالي الطلبات</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_requests'] }}</p>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <h3 class="text-lg font-semibold text-green-800">الطلبات الناجحة</h3>
                            <p class="text-3xl font-bold text-green-600">{{ $stats['successful_requests'] }}</p>
                            <p class="text-sm text-green-700">
                                {{ $stats['total_requests'] > 0 ? round(($stats['successful_requests'] / $stats['total_requests']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <h3 class="text-lg font-semibold text-red-800">الطلبات الفاشلة</h3>
                            <p class="text-3xl font-bold text-red-600">{{ $stats['failed_requests'] }}</p>
                            <p class="text-sm text-red-700">
                                {{ $stats['total_requests'] > 0 ? round(($stats['failed_requests'] / $stats['total_requests']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <h3 class="text-lg font-semibold text-purple-800">نسبة النجاح</h3>
                            <p class="text-3xl font-bold text-purple-600">
                                {{ $stats['total_requests'] > 0 ? round(($stats['successful_requests'] / $stats['total_requests']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                    </div>
                    
                    <!-- إحصائيات حسب الطريقة -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <h3 class="text-lg font-semibold text-indigo-800">الطريقة الأولى</h3>
                            <p class="text-3xl font-bold text-indigo-600">{{ $stats['method1_count'] }}</p>
                            <p class="text-sm text-indigo-700">
                                {{ $stats['total_requests'] > 0 ? round(($stats['method1_count'] / $stats['total_requests']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                        
                        <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                            <h3 class="text-lg font-semibold text-amber-800">الطريقة الثانية</h3>
                            <p class="text-3xl font-bold text-amber-600">{{ $stats['method2_count'] }}</p>
                            <p class="text-sm text-amber-700">
                                {{ $stats['total_requests'] > 0 ? round(($stats['method2_count'] / $stats['total_requests']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                        
                        <div class="bg-cyan-50 p-4 rounded-lg border border-cyan-200">
                            <h3 class="text-lg font-semibold text-cyan-800">طلبات Order</h3>
                            <p class="text-3xl font-bold text-cyan-600">{{ $stats['order_requests'] }}</p>
                            <p class="text-sm text-cyan-700">
                                {{ $stats['method2_count'] > 0 ? round(($stats['order_requests'] / $stats['method2_count']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                        
                        <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-200">
                            <h3 class="text-lg font-semibold text-emerald-800">طلبات Download</h3>
                            <p class="text-3xl font-bold text-emerald-600">{{ $stats['download_requests'] }}</p>
                            <p class="text-sm text-emerald-700">
                                {{ $stats['method2_count'] > 0 ? round(($stats['download_requests'] / $stats['method2_count']) * 100, 2) : 0 }}%
                            </p>
                        </div>
                    </div>
                    
                    <!-- الرسم البياني -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">تحليل استخدام API</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <canvas id="apiChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    
                    <!-- البيانات اليومية -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">البيانات اليومية</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجمالي الطلبات</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطريقة الأولى</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطريقة الثانية</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">طلبات Order</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">طلبات Download</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ناجحة</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">فاشلة</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($requestsByDate as $dateData)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $dateData['date'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['total'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['method1'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['method2'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['order'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['download'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['success'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dateData['failed'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تفاصيل الطلبات -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">تفاصيل الطلبات</h3>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوقت</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطريقة</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رمز الحالة</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستخدم</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($logData as $entry)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $entry->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($entry->method === 'method1')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                        الطريقة الأولى
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                                        الطريقة الثانية
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($entry->api_type === 'order')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-cyan-100 text-cyan-800">
                                                        طلب
                                                    </span>
                                                @elseif($entry->api_type === 'download')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                        تنزيل
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        -
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($entry->status === 'success')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        ناجح
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        فاشل
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->http_status ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($entry->user)
                                                    {{ $entry->user->name }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.reports.api.detail', $entry->id) }}" class="text-indigo-600 hover:text-indigo-900">عرض التفاصيل</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- ترقيم الصفحات -->
                            <div class="mt-4">
                                {{ $logData->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تغيير عرض فلتر نوع الطريقة الثانية
            const methodSelect = document.getElementById('method');
            const method2TypeContainer = document.getElementById('method2TypeContainer');
            
            methodSelect.addEventListener('change', function() {
                if (this.value === 'method2') {
                    method2TypeContainer.style.display = 'block';
                } else {
                    method2TypeContainer.style.display = 'none';
                }
            });
            
            // إعداد الرسم البياني
            const ctx = document.getElementById('apiChart').getContext('2d');
            const apiChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'الطريقة الأولى',
                            data: @json($chartData['method1']),
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'الطريقة الثانية',
                            data: @json($chartData['method2']),
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'ناجحة',
                            data: @json($chartData['success']),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'فاشلة',
                            data: @json($chartData['failed']),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'استخدام API حسب التاريخ'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>