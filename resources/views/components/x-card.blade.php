@props([
    'padding' => true,
    'shadow' => true,
    'border' => false,
])

@php
    $classes = 'bg-white rounded-lg';
    $classes .= $shadow ? ' shadow' : '';
    $classes .= $border ? ' border border-gray-200' : '';
    $classes .= $padding ? ' p-4 sm:p-6' : '';
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
