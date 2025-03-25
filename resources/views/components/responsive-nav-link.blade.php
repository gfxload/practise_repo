@props(['active', 'icon' => null])

@php
$classes = ($active ?? false)
    ? 'group flex items-center px-3 py-2 text-base font-medium rounded-md text-indigo-600 bg-indigo-50'
    : 'group flex items-center px-3 py-2 text-base font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900';

$iconClasses = ($active ?? false)
    ? 'mr-4 flex-shrink-0 h-6 w-6 text-indigo-600'
    : 'mr-4 flex-shrink-0 h-6 w-6 text-gray-400 group-hover:text-gray-500';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div class="{{ $iconClasses }}">
            {{ $icon }}
        </div>
    @endif
    {{ $slot }}
</a>
