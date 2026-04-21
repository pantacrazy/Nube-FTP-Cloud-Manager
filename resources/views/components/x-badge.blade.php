@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2 py-1 text-xs',
        'lg' => 'px-3 py-1 text-sm',
        default => 'px-2 py-1 text-xs',
    };
    
    $variantClasses = match($variant) {
        'success' => 'bg-green-100 text-green-800',
        'danger' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-blue-100 text-blue-800',
        'purple' => 'bg-violet-100 text-violet-800',
        'gray' => 'bg-gray-100 text-gray-800',
        'red' => 'bg-red-600 text-white',
        'blue' => 'bg-blue-600 text-white',
        'purple-color' => 'bg-violet-600 text-white',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="inline-block font-semibold rounded {{ $sizeClasses }} {{ $variantClasses }}">
    {{ $slot }}
</span>