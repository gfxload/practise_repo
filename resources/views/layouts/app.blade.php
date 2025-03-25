<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        
        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Mobile Top Bar -->
            <div class="md:hidden fixed top-0 left-0 right-0 z-50 bg-white shadow-md h-16">
                <div class="flex items-center justify-between px-4 h-full">
                    <div class="flex items-center space-x-3">
                        <x-application-logo class="block h-8 w-auto fill-current text-gray-600" />
                        <span class="text-gray-900 font-semibold">{{ config('app.name', 'Laravel') }}</span>
                    </div>
                    <button @click="$store.sidebar.toggle()" 
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-900 transition duration-150 ease-in-out">
                        <template x-if="!$store.sidebar.isOpen">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </template>
                        <template x-if="$store.sidebar.isOpen">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </template>
                    </button>
                </div>
            </div>

            <div class="flex">
                <!-- Desktop Navigation -->
                <div class="hidden md:flex">
                    @include('layouts.navigation')
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col min-h-screen">
                    <!-- Add padding top only for mobile -->
                    <div class="md:hidden h-16"></div>
                    
                    <!-- Page Heading -->
                    @isset($header)
                        <header class="bg-white shadow">
                            <div class="py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main class="flex-1">
                        {{ $slot }}
                    </main>

                    <!-- Footer -->
                    <footer class="bg-white shadow mt-auto">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            <div class="text-center text-sm text-gray-500">
                                {{ __('Developed by') }}
                                <a href="#WhatsApp" 
                                   class="text-indigo-600 hover:text-indigo-900 font-medium"
                                   rel="noopener noreferrer">
                                    Mahmoud Ragab
                                </a>
                              <!-- https://wa.me/201156318004 -->
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="$store.sidebar.isOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-0 z-40 md:hidden"
             @click.away="$store.sidebar.toggle()">
            
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
            </div>

            <!-- Sidebar -->
            <div class="relative flex flex-col w-full max-w-xs bg-white h-full">
                <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                    <nav class="px-4 space-y-3">
                        @include('layouts.navigation')
                    </nav>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    open: false,
                    toggle() {
                        this.open = !this.open;
                    },
                    get isOpen() {
                        return this.open;
                    }
                });
            });

            function getWhatsAppLink() {
                if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                    return "https://wa.me/201156318004";
                } else {
                    return "https://web.whatsapp.com/send?phone=201156318004&text=";
                }
            }
        </script>
    </body>
</html>
