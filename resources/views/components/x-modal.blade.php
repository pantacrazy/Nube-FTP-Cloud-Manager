@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div id="{{ $id }}" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-3 sm:p-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-lg p-4 sm:p-6 {{ $sizeClasses }} w-full max-h-[90vh] overflow-y-auto">
        @if($title)
            <h3 class="text-lg font-bold mb-4">{{ $title }}</h3>
        @endif
        {{ $slot }}
    </div>
</div>
