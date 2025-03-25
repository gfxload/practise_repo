@props(['status'])

@php
$isAdminRoute = str_contains(request()->path(), 'admin');
$statusText = $status;
$classes = 'px-2 py-1 text-xs font-medium rounded-full ';

if (!$isAdminRoute && $status === 'completed') {
    $statusText = 'Ready';
}

switch($status) {
    case 'pending':
        $classes .= 'bg-yellow-100 text-yellow-800';
        break;
    case 'processing':
        $classes .= 'bg-blue-100 text-blue-800';
        break;
    case 'completed':
        $classes .= 'bg-green-100 text-green-800';
        break;
    case 'failed':
        $classes .= 'bg-red-100 text-red-800';
        break;
    case 'active':
        $classes .= 'bg-green-100 text-green-800';
        break;
    case 'inactive':
        $classes .= 'bg-red-100 text-red-800';
        break;
    default:
        $classes .= 'bg-gray-100 text-gray-800';
}
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $statusText }}
</span>
