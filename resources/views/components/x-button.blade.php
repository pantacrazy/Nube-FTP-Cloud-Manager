@props([
    'type' => 'submit',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-bold rounded-lg shadow transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $sizeClasses = match($size) {
        'sm' => 'py-1.5 px-3 text-xs',
        'md' => 'py-2 px-4 text-sm',
        'lg' => 'py-3 px-6 text-base',
        default => 'py-2 px-4 text-sm',
    };
    
    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'bg-gray-500 text-white hover:bg-gray-600 focus:ring-gray-400',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-400',
        'purple' => 'bg-violet-600 text-white hover:bg-violet-700 focus:ring-violet-500',
        'outline' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
        default => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    };
    
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '';
    
    $classes = trim("{$baseClasses} {$sizeClasses} {$variantClasses} {$disabledClasses}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }} {{ $disabled ? 'disabled' : '' }}>
        {{ $slot }}
    </button>
@endif